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

  // static grid layer, cached so the animation loop only redraws the wave
  var staticCanvas = document.createElement("canvas");
  var staticCtx = staticCanvas.getContext("2d");
  var staticReady = false;

  function lineOn(c, x1, y1, x2, y2) {
    c.beginPath();
    c.moveTo(x1, y1);
    c.lineTo(x2, y2);
    c.stroke();
  }

  function renderStatic() {
    var dpr = window.devicePixelRatio || 1;
    staticCanvas.width = Math.round(w * dpr) || 1;
    staticCanvas.height = Math.round(h * dpr) || 1;
    var c = staticCtx;
    c.setTransform(dpr, 0, 0, dpr, 0, 0);
    c.clearRect(0, 0, w, h);

    var step = 48;
    c.lineWidth = 1;
    c.strokeStyle = pal.grid;
    for (var gx = 0; gx <= w; gx += step) lineOn(c, gx, 0, gx, h);
    for (var gy = 0; gy <= h; gy += step) lineOn(c, 0, gy, w, gy);

    c.strokeStyle = pal.center;
    lineOn(c, 0, h / 2, w, h / 2);
    staticReady = true;
  }

  function resize() {
    var dpr = window.devicePixelRatio || 1;
    var rect = canvas.getBoundingClientRect();
    w = rect.width;
    h = rect.height;
    canvas.width = Math.round(w * dpr) || 1;
    canvas.height = Math.round(h * dpr) || 1;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    renderStatic();
    draw(performance.now());
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
    lastNow = performance.now();
    if (idleTimer) { clearTimeout(idleTimer); idleTimer = 0; }
    schedule();
  }

  function draw(now) {
    if (!staticReady) renderStatic();

    ctx.clearRect(0, 0, w, h);
    ctx.drawImage(staticCanvas, 0, 0, w, h);

    var surge = surgeAt(now);
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
  }

  // the loop only runs while the wave moves or a sploosh is active;
  // otherwise it sleeps until the next scheduled wave.
  var running = !reduced;
  var rafId = 0;
  var idleTimer = 0;

  function schedule() {
    if (!running || rafId) return;
    rafId = requestAnimationFrame(frame);
  }

  function frame(now) {
    rafId = 0;
    if (!running) return;

    var dt = (now - lastNow) / 1000;
    if (!isFinite(dt) || dt < 0) dt = 0;
    if (dt > 0.1) dt = 0.1;
    lastNow = now;

    if (state === IDLE && now >= nextWaveAt) {
      state = WAVING;
      waveStart = now;
    } else if (state === WAVING && now - waveStart >= WAVE_DURATION) {
      state = IDLE;
      nextWaveAt = now + idleDelay();
    }

    var surge = surgeAt(now);
    if (surge > 0) {
      phase += dt * (5 + 8 * surge); // fast rush during sploosh
    } else if (state === WAVING) {
      phase += dt * 0.7; // occasional gentle drift
    }

    draw(now);

    if (state === WAVING || surge > 0) {
      schedule();
    } else {
      idleTimer = window.setTimeout(wake, Math.max(0, nextWaveAt - performance.now()) + 50);
    }
  }

  function wake() {
    idleTimer = 0;
    if (!running) return;
    lastNow = performance.now();
    schedule();
  }

  readColors();
  resize();

  window.addEventListener("resize", resize);
  document.addEventListener("themechange", function () {
    readColors();
    renderStatic();
    draw(performance.now());
  });

  document.addEventListener("visibilitychange", function () {
    if (reduced) return;
    if (document.hidden) {
      running = false;
      if (rafId) { cancelAnimationFrame(rafId); rafId = 0; }
      if (idleTimer) { clearTimeout(idleTimer); idleTimer = 0; }
    } else {
      running = true;
      lastNow = performance.now();
      schedule();
    }
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
    draw(performance.now());
  } else {
    schedule();
  }
})();
