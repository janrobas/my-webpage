(function () {
  "use strict";

  var canvas = document.getElementById("banner-canvas");
  if (!canvas || !canvas.getContext) return;

  var ctx = canvas.getContext("2d");
  var reduced = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  var PX = 5; // pixel size
  var w = 0;
  var h = 0;

  var pal = { accent: "#e53b44", grid: "rgba(191,203,190,.07)", center: "rgba(191,203,190,.18)" };

  function readColors() {
    var cs = getComputedStyle(document.documentElement);
    var get = function (name) {
      var v = cs.getPropertyValue(name).trim();
      return v || pal.accent;
    };
    pal.accent = get("--accent");
    pal.grid = get("--banner-grid");
    pal.center = get("--banner-center");
  }

  function resize() {
    var dpr = window.devicePixelRatio || 1;
    var rect = canvas.getBoundingClientRect();
    w = rect.width;
    h = rect.height;
    canvas.width = Math.round(w * dpr);
    canvas.height = Math.round(h * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  }

  function line(x1, y1, x2, y2) {
    ctx.beginPath();
    ctx.moveTo(x1, y1);
    ctx.lineTo(x2, y2);
    ctx.stroke();
  }

  function smoothstep(t) {
    t = Math.max(0, Math.min(1, t));
    return t * t * (3 - 2 * t);
  }

  // getting louder from left to right: amplitude grows across the banner
  function waveY(x, freq, phase, ampScale) {
    var env = 0.08 + 0.32 * (x / w);
    var amp = h * env * (ampScale || 1);
    return h / 2
      + amp * Math.sin(x * freq + phase)
      + 0.06 * amp * Math.sin(2 * x * freq + phase * 1.7);
  }

  var IDLE = 0;
  var WAVING = 1;
  var state = IDLE;

  var WAVE_DURATION = 4000; // ms
  var waveStart = 0;
  var nextWaveAt = performance.now() + 8000;
  var phase = 0;
  var lastNow = performance.now();

  function idleDelay() {
    return 10000 + Math.random() * 5000; // 10-15s between waves
  }

  function swell(now) {
    var t = (now - waveStart) / WAVE_DURATION;
    if (t <= 0.5) return smoothstep(t / 0.5);
    return 1 - smoothstep((t - 0.5) / 0.5);
  }

  // easter egg: click/tap makes the wave sploosh (big surge + fast rush)
  var SURGE_DURATION = 1600; // ms
  var surgeStart = -1e9;

  function surgeAt(now) {
    var t = (now - surgeStart) / SURGE_DURATION;
    if (t <= 0 || t >= 1) return 0;
    return Math.sin(t * Math.PI); // 0 -> 1 -> 0
  }

  function sploosh() {
    if (reduced) return;
    surgeStart = performance.now();
  }

  function draw(now) {
    ctx.clearRect(0, 0, w, h);

    var step = 48;
    ctx.lineWidth = 1;

    ctx.strokeStyle = pal.grid;
    for (var gx = 0; gx <= w; gx += step) line(gx, 0, gx, h);
    for (var gy = 0; gy <= h; gy += step) line(0, gy, w, gy);

    ctx.strokeStyle = pal.center;
    line(0, h / 2, w, h / 2);

    var dt = (now - lastNow) / 1000;
    lastNow = now;

    if (!reduced) {
      if (state === IDLE && now >= nextWaveAt) {
        state = WAVING;
        waveStart = now;
      } else if (state === WAVING && now - waveStart >= WAVE_DURATION) {
        state = IDLE;
        nextWaveAt = now + idleDelay();
      }
    }

    var surge = surgeAt(now);
    if (surge > 0) {
      phase += dt * (5 + 8 * surge); // fast rush during sploosh
    } else if (state === WAVING) {
      phase += dt * 0.7; // occasional gentle drift
    }

    var s = state === WAVING ? swell(now) : 0;
    var freq = 0.05;
    var ampScale = 1 + surge * 0.5;

    ctx.fillStyle = pal.accent;
    ctx.globalAlpha = 0.85;

    // connected pixel chain: vertical runs link each column to the next
    var prevQy = Math.round(waveY(0, freq, phase, ampScale) / PX) * PX;
    for (var x = 0; x <= w; x += PX) {
      var qy = Math.round(waveY(x, freq, phase, ampScale) / PX) * PX;
      var top = Math.min(prevQy, qy);
      var bh = Math.abs(qy - prevQy) + PX;
      ctx.fillRect(x, top, PX, bh);
      prevQy = qy;
    }

    ctx.globalAlpha = 1;

    if (!reduced) requestAnimationFrame(draw);
  }

  readColors();
  resize();
  window.addEventListener("resize", resize);
  document.addEventListener("themechange", function () {
    readColors();
    if (reduced) draw(0);
  });

  var banner = canvas.closest("#banner") || canvas;
  banner.addEventListener("pointerdown", sploosh);
  banner.addEventListener("keydown", function (ev) {
    if (ev.key === "Enter" || ev.key === " ") {
      ev.preventDefault();
      sploosh();
    }
  });

  if (reduced) {
    draw(0);
  } else {
    requestAnimationFrame(draw);
  }
})();