<?php
// index.php — WeatherNow (single-file, PHP-enabled)
$city = isset($_GET['city']) ? trim($_GET['city']) : 'Ranaghat';
// PHP 5.x compatibility: Replaced ?? with isset() for XAMPP
function h($s){ return htmlspecialchars(isset($s) ? $s : '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>WeatherNow — Animated Dark</title>
<meta name="color-scheme" content="light dark" />
<style>
  :root {
    --bg1:#a3d8ff; --bg2:#dbeaff; --card:rgba(255,255,255,.7); /* Lighter card */
    --text:#0b1220; --muted:#525a6a; --brand:#2563eb; --ring:rgba(37,99,235,.2);
    --surface:rgba(255,255,255,.5); --shadow:0 20px 40px -10px rgba(16,24,40,.1); /* Softer shadow */
    --border:rgba(255,255,255,.7);
    --border-gradient: linear-gradient(120deg, rgba(255,255,255,.9), rgba(255,255,255,.4));
    --card-bg: linear-gradient(135deg, rgba(255,255,255,0.5) 0%, rgba(255,255,255,0) 100%), var(--card);
  }
  @media (prefers-color-scheme:dark){
    :root {
      /* Base colors are now set to the aurora background color */
      --bg1:#070b16; --bg2:#070b16; 
      /* --- UPDATED: More transparent card for aurora --- */
      --card:rgba(18, 28, 54, 0.55); 
      --text:#e5e7eb; --muted:#9ab3d0; --brand:#60a5fa; --ring:rgba(96, 165, 250, 0.25);
      --surface:rgba(255,255,255,.08); --border:rgba(255,255,255,.12);
      --border-gradient: linear-gradient(120deg, rgba(255,255,255,.2), rgba(255,255,255,.08));
      --shadow: 0 25px 50px -12px rgba(0,0,0,.35);
      /* --- UPDATED: Enhanced Card Background (Dark) --- */
      --card-bg: linear-gradient(135deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0) 100%), var(--card);
    }
    
    /* --- NEW: Override body background for dark mode --- */
    body.dark {
        background: #070b16; /* Solid dark color for aurora base */
    }
  }
  *{box-sizing:border-box}
  body {
    margin:0; font-family:Inter,ui-sans-serif,system-ui,Segoe UI,Roboto,Arial;
    color:var(--text);
    /* Default (light mode) background */
    background: radial-gradient(1200px 600px at -10% -10%, var(--bg1), transparent 60%),
                radial-gradient(1000px 600px at 110% 10%, var(--bg2), transparent 55%),
                linear-gradient(180deg, var(--bg2), var(--bg1));
    min-height:100svh;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    position: relative; /* Needed for z-index stacking */
    overflow-x: hidden;
  }
  
  /* --- NEW: Aurora Background (From welcome.php) --- */
  .aurora{
    position:fixed; inset:-20%; z-index:-2; pointer-events:none;
    filter:blur(45px) saturate(130%);
    background:
      radial-gradient(40% 60% at 20% 20%, rgba(56,189,248,.45), transparent 60%),
      radial-gradient(35% 55% at 80% 30%, rgba(168,85,247,.40), transparent 60%),
      radial-gradient(30% 50% at 50% 80%, rgba(34,197,94,.38), transparent 60%);
    animation:aurora 14s ease-in-out infinite alternate;
    opacity: 0; /* Hidden by default */
    transition: opacity 0.5s;
  }
  body.dark .aurora { opacity: 1; } /* Only show in dark mode */
  
  @keyframes aurora{ 0%{transform:translate3d(-2%,0,0) rotate(.5deg)} 50%{transform:translate3d(2%,-1%,0) rotate(-.5deg)} 100%{transform:translate3d(-1%,1%,0) rotate(.6deg)} }

  .vignette{
    position:fixed; inset:0; z-index:-1; pointer-events:none;
    background: linear-gradient(180deg, rgba(0,0,0,.0), rgba(0,0,0,.30));
    opacity: 0; /* Hidden by default */
    transition: opacity 0.5s;
  }
  body.dark .vignette { opacity: 1; } /* Only show in dark mode */

  .noise{
    position:fixed; inset:0; z-index:-1; pointer-events:none; opacity:.05; mix-blend-mode:overlay;
    background-image:url('data:image/svg+xml;utf8,\
    <svg xmlns="http://www.w3.org/2000/svg" width="140" height="140" viewBox="0 0 140 140">\
    <filter id="n"><feTurbulence type="fractalNoise" baseFrequency=".9" numOctaves="2"/></filter>\
    <rect width="100%" height="100%" filter="url(%23n)" opacity=".35"/></svg>');
    opacity: 0;
    transition: opacity 0.5s;
  }
  body.dark .noise { opacity: 0.05; }
  /* --- End of Aurora --- */

  .wrap{max-width:1080px;margin:auto;padding:28px 16px; position: relative; z-index: 2;}

  /* Enhanced Glass Effect */
  .glass {
    background: var(--card-bg);
    border: 1px solid transparent;
    border-image: var(--border-gradient) 1;
    border-radius:24px;
    backdrop-filter: blur(18px) saturate(120%);
    box-shadow: var(--shadow);
    position: relative;
  }

  header.glass{padding:18px 24px; display:flex; align-items:center; justify-content:space-between; gap:12px}
  .title {
    display:flex; align-items:center; gap:12px; font-weight:800; letter-spacing:.2px;
    font-size:clamp(22px,3vw,30px);
    background:linear-gradient(90deg,#111827, #2563eb 40%, #22c55e);
    -webkit-background-clip:text; background-clip:text; color:transparent;
  }
  body.dark .title{ background:linear-gradient(90deg,#e5e7eb, #60a5fa 40%, #34d399); -webkit-background-clip:text; }
  
  /* --- REMOVED: Spinning Logo Mark CSS --- */
  /* .mark and @keyframes spin CSS classes are removed */

  /* Controls Section */
  .controls-section { margin-top:16px; padding:20px 24px; }
  .toolbar{display:flex; gap:10px; flex-wrap:wrap}
  input[type="text"]{
    flex:1; min-width:260px; padding:12px 16px;
    background:var(--surface); color:var(--text);
    border:1px solid var(--border); border-radius:14px;
    outline:none;
    transition:.25s border, .25s box-shadow, .25s background, .25s transform ease-out;
    font-size: 15px;
  }
  input[type="text"]::placeholder { color: var(--muted); opacity: 0.8; }
  input[type="text"]:focus{
    border-color:var(--brand);
    box-shadow:0 0 0 4px var(--ring);
    background: transparent;
    transform: translateY(-1px);
  }

  /* Buttons */
  .btn{
    border:1px solid var(--border); background:linear-gradient(180deg,rgba(255,255,255,.8), rgba(255,255,255,.5));
    padding:11px 16px; border-radius:14px; cursor:pointer; font-weight:600; font-size: 14px;
    transition: .2s transform, .2s box-shadow, .2s opacity, .2s background, .2s filter ease-out;
    color: var(--text);
    backdrop-filter: blur(5px);
  }
  body.dark .btn { background:linear-gradient(180deg,rgba(255,255,255,.09), rgba(255,255,255,.05)); }
  .btn.brand{ background:linear-gradient(180deg,#3b82f6,#2563eb); color:#fff; border:none }
  body.dark .btn.brand { background:linear-gradient(180deg,#60a5fa,#3b82f6); }
  .btn.ghost{ background:var(--surface); color:var(--text)}
  .btn:hover{
    transform: translateY(-2px) scale(1.02);
    box-shadow:0 12px 24px -6px rgba(2,8,23,.15);
    border-color: var(--border);
    filter: brightness(1.1);
  }
  body.dark .btn:hover {
    box-shadow:0 12px 24px -6px rgba(0,0,0,.3);
    border-color: rgba(255,255,255,.2);
    filter: brightness(1.2);
  }
  .btn:active{ transform: translateY(0); box-shadow: none; filter: brightness(1); }
  .btn:disabled{opacity:.5; cursor:not-allowed; transform: none; box-shadow: none; }

  /* Grid & Cards */
  .grid{ display:grid; gap:16px; margin-top:16px; grid-template-columns: repeat(12,1fr); }
  .card{ padding:20px 24px }
  .span-12{grid-column:span 12}
  .span-6{grid-column:span 12} .span-4{grid-column:span 12}
  @media(min-width:900px){ .span-6{grid-column:span 6} .span-4{grid-column:span 4} }

  /* 3D Hover Tilt for main cards */
  .card.glass {
      transition: transform 0.4s cubic-bezier(0.19, 1, 0.22, 1), 
                  box-shadow 0.4s cubic-bezier(0.19, 1, 0.22, 1);
  }
  .card.glass:hover {
      transform: perspective(1500px) rotateX(1deg) rotateY(-4deg) scale(1.02);
      z-index: 10;
      box-shadow: 0 40px 70px -15px rgba(0,0,0,.3);
  }
  body.dark .card.glass:hover {
      box-shadow: 0 40px 70px -15px rgba(0,0,0,.5);
  }

  .row{display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap}
  .muted{color:var(--muted); font-size:14px}
  .pill{padding:5px 10px; border-radius:999px; background:var(--surface); border:1px solid var(--border); font-size:12px; font-weight: 500;}
  .lead{font-size: clamp(36px, 5vw, 44px); font-weight:800}
  .kpis{ display:grid; grid-template-columns:repeat(auto-fit, minmax(120px, 1fr)); gap:12px; margin-top:16px }
  .kpis .item{ 
    /* Internal squares removed */
    border-radius:16px; padding:14px; text-align:center 
  }
  .big{font-size:20px; font-weight:700}

  /* Hourly & Daily Forecast */
  .hourly, .days{ display:grid; gap:12px }
  .hourly{ grid-template-columns: repeat(auto-fill, minmax(90px,1fr)); }
  .days{ grid-template-columns: repeat(auto-fill, minmax(140px,1fr)); }
  .hour, .day{
    /* Internal squares removed */
    border-radius:16px; padding:12px; text-align:center;
    transition: transform .2s ease-out, background .2s ease-out, box-shadow .2s ease-out;
  }
  .hour:hover, .day:hover {
    transform: translateY(-4px) scale(1.03);
    background: rgba(255,255,255,.05);
    box-shadow: 0 8px 16px rgba(0,0,0,.1);
  }
  body.dark .hour:hover, body.dark .day:hover {
    background: rgba(255,255,255,.07);
    box-shadow: 0 8px 16px rgba(0,0,0,.2);
  }
  .ico{ font-size:28px }
  .icon-pulse { animation: pulse 0.6s ease-out; }
  @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } }

  /* State Messages */
  .state{ text-align:center; padding:20px; font-weight: 500; min-height: 2em; display: flex; align-items: center; justify-content: center; gap: 8px;}
  .state.error { color: #f87171; }
  .loader {
      width: 16px; height: 16px; border: 2px solid currentColor;
      border-bottom-color: transparent; border-radius: 50%;
      display: inline-block; animation: spin .75s linear infinite;
  }
  @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

  /* Footer */
  .footer{ text-align:center; margin-top:24px; font-size:12px; color:var(--muted); opacity: 0.8; }

  /* Theme Toggle */
  .toggle{
    width:44px; height:26px; background:var(--surface); border:1px solid var(--border); border-radius:999px; position:relative; cursor:pointer;
  }
  .knob{ position:absolute; top:2px; left:2px; width:20px; height:20px; border-radius:50%; background:#fff; transition:.25s left cubic-bezier(0.25, 0.46, 0.45, 0.94), .2s background; box-shadow: 0 2px 4px rgba(0,0,0,.1); }
  body.dark .knob{ left:20px; background:#1e293b }

  /* Entrance Animation */
  .anim-scaleUpIn {
    opacity: 0;
    transform: translateY(15px) scale(0.98);
    animation: scaleUpIn .5s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
  }
  @keyframes scaleUpIn{
    from{opacity:0; transform: translateY(15px) scale(0.98)}
    to{ opacity:1; transform:none }
  }
  .stagger-children > * {
    opacity: 0;
  }

</style>
</head>
<body class="<?php echo (isset($_COOKIE['wx_theme']) && $_COOKIE['wx_theme'] === 'light') ? '' : 'dark'; ?>">
  
  <div class="aurora"></div>
  <div class="noise"></div>
  <div class="vignette"></div>
  
  <div class="wrap">
    <header class="glass anim-scaleUpIn">
      <div class="title">🌤️ WeatherNow</div>
      <div class="row" style="gap:8px">
        <div class="toggle" id="themeToggle" title="Toggle dark mode"><div class="knob"></div></div>
      </div>
    </header>

    <section class="glass card span-12 controls-section anim-scaleUpIn" style="animation-delay: 0.1s;">
      <div class="row" style="gap:10px">
        <div class="toolbar" style="flex:1">
          <input id="q" type="text" placeholder="Search city or lat,lon" value="<?php echo h($city); ?>" />
          <button class="btn brand" id="btnSearch">Search</button>
          <button class="btn ghost" id="btnGeo">Use my location</button>
          <button class="btn ghost" id="btnUnit">°C → °F</button>
        </div>
        <span id="locLabel" class="pill anim-scaleUpIn" style="animation-delay: 0.2s;">—</span>
      </div>
    </section>

    <div class="grid">
      <section class="glass card span-12 anim-scaleUpIn" id="todayCard" style="animation-delay: 0.25s;">
        <div class="row">
          <div style="display:flex; gap:10px; align-items:center">
            <div id="todayEmoji" class="ico">☁️</div>
            <div>
              <div id="place" style="font-weight:700; font-size:18px">—</div>
              <div class="muted" id="dateNow">—</div>
            </div>
          </div>
          <div class="lead" id="todayTemp">—°</div>
        </div>
        <div class="row" style="margin-top:8px">
          <div class="muted">Today:</div>
          <div id="todayLabel" style="font-weight:600">—</div>
        </div>
        <div class="kpis stagger-children">
          <div class="item"><div class="muted">High / Low</div><div class="big" id="hiLo">—</div></div>
          <div class="item"><div class="muted">Wind</div><div class="big" id="wind">—</div></div>
          <div class="item"><div class="muted">Humidity</div><div class="big" id="hum">—</div></div>
          <div class="item"><div class="muted">Precip (24h)</div><div class="big" id="precip">—</div></div>
        </div>
      </section>

      <section class="glass card span-12 anim-scaleUpIn" style="animation-delay: 0.3s;">
        <div class="row"><div style="font-weight:700">Next 24 hours</div><div class="muted">Local time</div></div>
        <div class="hourly stagger-children" id="hourly" style="margin-top:10px"></div>
      </section>

      <section class="glass card span-12 anim-scaleUpIn" style="animation-delay: 0.35s;">
        <div style="font-weight:700">7-day forecast</div>
        <div class="days stagger-children" id="days" style="margin-top:10px"></div>
      </section>
    </div>

    <div class="state anim-scaleUpIn" id="state" style="animation-delay: 0.4s;"></div>

    <div class="footer anim-scaleUpIn" style="animation-delay: 0.45s;">Data by Open-Meteo • No API key • Built with 💙</div>
  </div>

<script>
// ------- PHP → JS bridge: initial city from server -------
const INITIAL_CITY = <?php echo json_encode($city, JSON_UNESCAPED_UNICODE); ?>;

/* ---------- Theme ---------- */
const themeBtn = document.getElementById("themeToggle");
const applyTheme = (theme) => {
    document.body.classList.toggle("dark", theme === "dark");
    localStorage.setItem("wx_theme", theme);
    document.cookie = `wx_theme=${theme}; path=/; max-age=31536000; samesite=lax`;
};
// UPDATED: Now respects PHP-set default mode and then uses localStorage
const initialTheme = localStorage.getItem("wx_theme") || (document.body.classList.contains('dark') ? 'dark' : 'light');
applyTheme(initialTheme); 
themeBtn.onclick = ()=> applyTheme(document.body.classList.contains("dark") ? "light" : "dark");

/* ---------- Helpers ---------- */
const $ = (id)=>document.getElementById(id);
const fmtDay = (iso)=> new Date(iso).toLocaleDateString(undefined,{weekday:"short"});
const fmtMD  = (iso)=> new Date(iso).toLocaleDateString(undefined,{month:"short", day:"numeric"});
const fmtTime= (iso)=> new Date(iso).toLocaleTimeString([], {hour:"numeric", minute:"2-digit"});
const cToF = c => (c*9)/5 + 32;
const kmhToMph = k => k*0.621371;
function codeInfo(code){
  const m = {
    0:["Clear","☀️"],1:["Mainly clear","🌤️"],2:["Partly cloudy","⛅"],3:["Overcast","☁️"],
    45:["Fog","🌫️"],48:["Rime fog","🌫️"],
    51:["Drizzle","🌦️"],53:["Drizzle","🌦️"],55:["Drizzle","🌦️"],
    61:["Rain","🌧️"],63:["Rain","🌧️"],65:["Heavy rain","🌧️"],
    66:["Freezing rain","🧊"],67:["Freezing rain","🧊"],
    71:["Snow","🌨️"],73:["Snow","🌨️"],75:["Snow","🌨️"],77:["Snow grains","🌨️"],
    80:["Showers","🌦️"],81:["Showers","🌦️"],82:["Showers","🌦️"],
    85:["Snow sh.","🌨️"],86:["Snow sh.","🌨️"],
    95:["Thunder","⛈️"],96:["Thunder+hail","⛈️"],99:["Thunder+hail","⛈️"]
  };
  return m[code] || ["Cloudy","☁️"];
}

/* ---------- Unit ---------- */
let UNIT = localStorage.getItem("wx_unit") || "C";
function setUnitBtn(){ $("btnUnit").textContent = UNIT==="C" ? "°C → °F" : "°F → °C"; }
function asUnit(c){ return UNIT==="C" ? Math.round(c) : Math.round(cToF(c)); }
$("btnUnit").onclick = ()=>{
  UNIT = UNIT==="C" ? "F" : "C";
  localStorage.setItem("wx_unit", UNIT);
  setUnitBtn();
  const q = localStorage.getItem("wx_last_query") || $("q").value.trim();
  if(q) loadByCity(q);
};
setUnitBtn();

/* ---------- API ---------- */
async function geocodeCity(q){
  const coords = q.match(/^(-?\d+(\.\d+)?)\s*,\s*(-?\d+(\.\d+)?)$/);
  if (coords) {
      const lat = parseFloat(coords[1]);
      const lon = parseFloat(coords[3]);
      try {
          const revR = await fetch(`https://geocoding-api.open-meteo.com/v1/reverse?latitude=${lat}&longitude=${lon}&format=json`);
          const revJ = await revR.json();
          const place = revJ.results?.[0];
          const label = place ? `${place.name}${place.admin1 ? ', ' + place.admin1 : ''}` : "Coordinates Location";
          return { lat, lon, label, country: place?.country || "" };
      } catch (e) { return { lat, lon, label: `${lat.toFixed(2)}, ${lon.toFixed(2)}`, country: "" }; }
  } else {
      const r = await fetch(`https://geocoding-api.open-meteo.com/v1/search?name=${encodeURIComponent(q)}&count=1&language=en&format=json`);
      if(!r.ok) throw new Error("Geocoding service unavailable");
      const j = await r.json();
      if(!j.results || !j.results.length) throw new Error(`City "${q}" not found`);
      const c = j.results[0];
      return { lat:c.latitude, lon:c.longitude, label:`${c.name}${c.admin1?`, ${c.admin1}`:''}`, country:c.country||"" };
  }
}
async function fetchForecast(lat, lon){
  const params = new URLSearchParams({
    latitude:lat, longitude:lon, timezone:"auto",
    current:"temperature_2m,relative_humidity_2m,wind_speed_10m,weather_code,precipitation",
    hourly:"temperature_2m,weather_code",
    daily:"temperature_2m_max,temperature_2m_min,precipitation_sum,wind_speed_10m_max,weathercode"
  });
  const r = await fetch(`https://api.open-meteo.com/v1/forecast?${params}`);
  if(!r.ok) throw new Error("Weather service unavailable");
  return r.json();
}

/* ---------- Apply Staggered Animation ---------- */
function applyStaggeredAnimation(containerSelector) {
    const container = document.querySelector(containerSelector);
    if (!container) return;
    if (!container.classList.contains('stagger-children')) {
        container.classList.add('stagger-children');
    }
    const items = container.children;
    for (let i = 0; i < items.length; i++) {
        items[i].classList.remove('anim-scaleUpIn');
        items[i].style.animationDelay = '';
        items[i].style.opacity = '0'; 
        void items[i].offsetWidth;
        items[i].classList.add('anim-scaleUpIn');
        items[i].style.animationDelay = `${i * 0.05}s`;
    }
}

/* ---------- Render ---------- */
function setState(msg, type="", isLoadingState=false){
    const stateEl = $("state");
    let content = msg || "";
    if (isLoadingState) {
        content = `<span class="loader"></span> ${msg}`; // Add spinner
    }
    stateEl.innerHTML = content;
    stateEl.className = `state anim-scaleUpIn ${type}`;
    if(msg) stateEl.style.animationDelay = '0.2s';
}
function render(place, data){
  document.querySelectorAll('.stagger-children > *, #todayCard').forEach(el => {
      el.classList.remove('anim-scaleUpIn');
      el.style.animationDelay = '';
      el.style.opacity = '0';
  });
  void document.body.offsetWidth;

  $('todayCard').classList.add('anim-scaleUpIn');
  $('todayCard').style.animationDelay = '0s';

  $("place").textContent = `${place.label}${place.country?`, ${place.country}`:""}`;
  $("locLabel").textContent = place.label;
  $("dateNow").textContent = new Date().toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' });

  const [lab, emo] = codeInfo(data.current.weather_code);
  $("todayLabel").textContent = lab;
  const emojiEl = $("todayEmoji");
  emojiEl.textContent = emo;
  emojiEl.classList.remove('icon-pulse');
  void emojiEl.offsetWidth;
  emojiEl.classList.add('icon-pulse');


  $("todayTemp").textContent = `${asUnit(data.current.temperature_2m)}°${UNIT}`;

  const hi = asUnit(data.daily.temperature_2m_max[0]);
  const lo = asUnit(data.daily.temperature_2m_min[0]);
  $("hiLo").textContent = `${hi}° / ${lo}°`;

  const wind = data.current.wind_speed_10m;
  $("wind").textContent = `${Math.round(UNIT==="C" ? wind : kmhToMph(wind))} ${UNIT==="C"?"km/h":"mph"}`;

  const rh = data.current.relative_humidity_2m;
  $("hum").textContent = rh!=null ? `${rh}%` : "—";

  const precip = (data.daily.precipitation_sum?.[0] ?? data.current.precipitation ?? 0);
  $("precip").textContent = `${Number(precip).toFixed(1)} mm`;

  // hourly
  const hr = $("hourly"); hr.innerHTML="";
  data.hourly.time.slice(0,24).forEach((tIso,i)=>{
    const [hLab, hEmo] = codeInfo(data.hourly.weather_code[i]);
    const el = document.createElement("div"); el.className="hour";
    el.innerHTML = `<div class="muted">${fmtTime(tIso)}</div>
                      <div class="ico">${hEmo}</div>
                      <div class="big">${asUnit(data.hourly.temperature_2m[i])}°</div>`;
    hr.appendChild(el);
  });
  applyStaggeredAnimation("#hourly");

  // 7-day
  const days = $("days"); days.innerHTML="";
  data.daily.time.forEach((d,i)=>{
    const [L,E] = codeInfo(data.daily.weathercode[i]);
    const el = document.createElement("div"); el.className="day";
    el.innerHTML = `
      <div class="muted">${i === 0 ? 'Today' : fmtDay(d)} • ${fmtMD(d)}</div>
      <div class="ico" style="margin:6px 0">${E}</div>
      <div style="font-weight:600">${L}</div>
      <div class="big" style="margin-top:4px">${asUnit(data.daily.temperature_2m_max[i])}°
        <span class="muted"> / ${asUnit(data.daily.temperature_2m_min[i])}°</span></div>
      <div class="muted" style="margin-top:2px; font-size: 11px;">💧 ${Number(data.daily.precipitation_sum[i]||0).toFixed(1)} mm</div>`;
    days.appendChild(el);
  });
  applyStaggeredAnimation("#days");
  applyStaggeredAnimation(".kpis");
}

/* ---------- Actions ---------- */
let isLoading = false;
async function loadByCity(q){
  if(!q || isLoading) return;
  isLoading = true;
  $("btnSearch").disabled = true; $("btnGeo").disabled = true;
  try{
    setState("Fetching forecast…", "", true);
    const g = await geocodeCity(q);
    const f = await fetchForecast(g.lat, g.lon);
    render(g, f);
    setState("");
    localStorage.setItem("wx_last_query", q);
  }catch(e){ console.error(e); setState(e.message || "Something went wrong","error"); }
  finally {
    isLoading = false;
    $("btnSearch").disabled = false; $("btnGeo").disabled = false;
  }
}
function useMyLocation(){
  if (isLoading) return;
  isLoading = true;
  $("btnSearch").disabled = true; $("btnGeo").disabled = true;
  setState("Getting your location…", "", true);
  if(!navigator.geolocation){ setState("Geolocation not supported","error"); isLoading = false; return; }
  navigator.geolocation.getCurrentPosition(async pos=>{
    try{
      const {latitude:lat, longitude:lon} = pos.coords;
      const f = await fetchForecast(lat, lon);
      let g = {lat,lon,label:"My Location",country:""};
      try {
          const revR = await fetch(`https://geocoding-api.open-meteo.com/v1/reverse?latitude=${lat}&longitude=${lon}&format=json`);
          const revJ = await revR.json();
          const place = revJ.results?.[0];
          g.label = place ? `${place.name}${place.admin1 ? ', ' + place.admin1 : ''}` : "My Location";
          g.country = place?.country || "";
      } catch (revE) { console.warn("Reverse geocoding failed", revE); }
      render(g, f); setState("");
      localStorage.setItem("wx_last_query", g.label !== "My Location" ? g.label : `${lat.toFixed(4)},${lon.toFixed(4)}`);
    }catch(e){ setState(e.message||"Location fetch failed","error"); }
    finally {
        isLoading = false;
        $("btnSearch").disabled = false; $("btnGeo").disabled = false;
    }
  }, err=> {
      setState(err.message||"Permission denied or location unavailable","error");
      isLoading = false;
      $("btnSearch").disabled = false; $("btnGeo").disabled = false;
  }, { timeout: 10000 });
}

/* ---------- Wire up ---------- */
$("btnSearch").onclick = ()=> loadByCity($("q").value.trim());
$("q").addEventListener("keydown",(e)=>{ if(e.key==="Enter") $("btnSearch").click(); });
$("btnGeo").onclick = useMyLocation;

// Initial Load
document.addEventListener('DOMContentLoaded', () => {
    const startCity = (INITIAL_CITY || '').trim() || localStorage.getItem("wx_last_query") || $("q").value.trim();
    if(startCity){
        $("q").value = startCity;
        loadByCity(startCity);
    } else {
        setState("Enter a city, coordinates (lat,lon), or use your location.");
    }
    document.querySelectorAll('.anim-scaleUpIn').forEach((el, i) => {
        if (!el.closest('.stagger-children')) {
            el.style.animationDelay = `${i * 0.05}s`;
        }
    });
});
</script>
</body>
</html>