<?php
// PHP 5.x compatibility: Replaced ?? with isset()
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $city = trim(isset($_POST['city']) ? $_POST['city'] : '');
  if ($city !== '') { header('Location: index.php?city=' . urlencode($city)); exit; }
}
// PHP 5.x compatibility: Replaced ?? with isset()
function h($s){ return htmlspecialchars(isset($s) ? $s : '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>WeatherNow — Welcome</title>

<style>
*{box-sizing:border-box}
html{height:100%}
body{
  margin:0; font-family:Inter,ui-sans-serif,system-ui,Segoe UI,Roboto;
  background:#070b16; color:#e6edf6;
  overflow-x: hidden; /* Hide horizontal scrollbar, just in case */
}

/* ---------- Background layers ---------- */
#bg3d{position:fixed; inset:0; pointer-events:none; z-index:-3;}
.aurora{
  position:fixed; inset:-20%; z-index:-2; pointer-events:none;
  filter:blur(45px) saturate(130%);
  background:
    radial-gradient(40% 60% at 20% 20%, rgba(56,189,248,.45), transparent 60%),
    radial-gradient(35% 55% at 80% 30%, rgba(168,85,247,.40), transparent 60%),
    radial-gradient(30% 50% at 50% 80%, rgba(34,197,94,.38), transparent 60%);
  animation:aurora 14s ease-in-out infinite alternate;
}
@keyframes aurora{ 0%{transform:translate3d(-2%,0,0) rotate(.5deg)} 50%{transform:translate3d(2%,-1%,0) rotate(-.5deg)} 100%{transform:translate3d(-1%,1%,0) rotate(.6deg)} }

.vignette{position:fixed; inset:0; z-index:-1; pointer-events:none;
  background:
    radial-gradient(1200px 700px at 30% -10%, rgba(59,130,246,.35), transparent 60%),
    radial-gradient(1000px 600px at 120% 5%, rgba(34,197,94,.30), transparent 60%),
    linear-gradient(180deg, rgba(0,0,0,.08), rgba(0,0,0,.50));
}
.scan{position:fixed; inset:0; z-index:-1; pointer-events:none;
  background:repeating-linear-gradient(180deg, rgba(255,255,255,.06) 0 2px, transparent 2px 6px);
  opacity:.15; mix-blend-mode:soft-light;
}
.noise{
  position:fixed; inset:0; z-index:-1; pointer-events:none; opacity:.05; mix-blend-mode:overlay;
  background-image:url('data:image/svg+xml;utf8,\
  <svg xmlns="http://www.w3.org/2000/svg" width="140" height="140" viewBox="0 0 140 140">\
  <filter id="n"><feTurbulence type="fractalNoise" baseFrequency=".9" numOctaves="2"/></filter>\
  <rect width="100%" height="100%" filter="url(%23n)" opacity=".35"/></svg>');
}

/* Floating blobs + pulse (Unchanged) */
.blob{position:absolute; width:220px;height:220px; background:radial-gradient(circle,rgba(96,165,250,.28),transparent 60%); filter:blur(60px); animation:float 6s ease-in-out infinite; z-index:0;}
.blob:nth-child(1){top:10%; left:-4%;}
.blob:nth-child(2){bottom:6%; right:-3%; animation-delay:2s;}
@keyframes float{50%{transform:translateY(-20px) scale(1.1);}}
.pulse{position:absolute; width:14px;height:14px; border-radius:50%; background:#34d399; top:22px; right:22px; box-shadow:0 0 10px #34d399; animation:pulse 1.8s infinite;}
@keyframes pulse{0%{transform:scale(.9);opacity:.75}50%{transform:scale(1.3);opacity:1}100%{transform:scale(.9);opacity:.75}}

/* Gentle background bob (Unchanged) */
@keyframes bgfloat { 0%{transform:translateY(0)} 50%{transform:translateY(-10px)} 100%{transform:translateY(0)} }
.aurora, .vignette, .scan { animation: bgfloat 20s ease-in-out infinite; }

/* ---------- Header ---------- */
.top{position:fixed; top:0; left:0; right:0; z-index:4; display:flex; justify-content:center; align-items:center; padding:14px 18px;}
.brand{display:flex; align-items:center; gap:10px; font-weight:900; font-size:20px}
.mark{width:26px;height:26px; border-radius:10px; background:conic-gradient(from 0deg,#5eead4,#60a5fa,#a78bfa,#f472b6,#5eead4); animation:spin 4s linear infinite; filter:drop-shadow(0 0 10px #60a5fa);}
@keyframes spin{to{transform:rotate(1turn)}}


/* --- Page Container --- */
.page-container {
  position: relative;
  z-index: 2;
  width: 100%;
  padding-top: 80px;
  padding-bottom: 40px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* --- Glassy Effect & Spacing --- */
.content-box {
  position: relative;
  width: min(900px, 92vw);
  overflow: auto;
  border: 1px solid transparent;

  background:
    linear-gradient(140deg, rgba(20, 25, 50, 0.65), rgba(15, 20, 40, 0.55)) padding-box,
    linear-gradient(120deg, rgba(255, 255, 255, 0.25), rgba(255, 255, 255, 0.15)) border-box;

  backdrop-filter: blur(16px);
  border-radius: 24px;
  padding: 32px 26px;
  box-shadow: 0 35px 90px rgba(0,0,0,.55);

  margin-top: 24px;

  scrollbar-width: thin;
  scrollbar-color: rgba(255,255,255,.2) transparent;

  transition: transform 0.5s cubic-bezier(0.2, 0.8, 0.2, 1),
              box-shadow 0.5s cubic-bezier(0.2, 0.8, 0.2, 1);
}

.content-box:hover {
  transform: translateY(-15px) scale(1.02);
  box-shadow: 0 50px 120px rgba(0,0,0,.8), 0 0 0 2px rgba(96,165,250,.3) inset;
}


/* --- NEW: Slide-in Animation --- */
/* We will add this class using JavaScript */
.slide-in {
  opacity: 0;
  transform: translateY(30px);
  transition: opacity 0.8s ease-out, transform 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
}
.slide-in.visible {
  opacity: 1;
  transform: translateY(0);
}


.content-box::-webkit-scrollbar{width:6px}
.content-box::-webkit-scrollbar-thumb{background:linear-gradient(180deg,#60a5fa,#34d399); border-radius:6px}
.content-box::-webkit-scrollbar-track{background:transparent}

/* First content box no top margin */
.page-container .content-box:first-child {
  margin-top: 0;
}

/* --- (Original Styles) --- */
.title{
  font-size:34px; letter-spacing:.3px; margin:6px 10px 8px;
  background:linear-gradient(90deg,#e6edf6,#60a5fa,#34d399,#a78bfa);
  background-size:300% 100%; -webkit-background-clip:text; color:transparent;
  animation:shine 8s linear infinite;
}
@keyframes shine{0%{background-position:0% 50%}100%{background-position:300% 50%}}
@media(min-width:900px){ .title{font-size:44px} }

.subtitle{margin:0 10px 10px; color:#afbdd1; min-height:20px}

/* CTAs */
.ctas{display:flex; gap:10px; flex-wrap:wrap; margin-top:10px}
.btn{border:0; border-radius:14px; padding:12px 16px; font-weight:800; cursor:pointer; transition:.25s transform,.25s box-shadow;}
.btn.primary{position:relative; background:linear-gradient(90deg,#60a5fa,#34d399); color:#051022; box-shadow:0 10px 28px rgba(56,189,248,.35)}
.btn.primary::after{content:""; position:absolute; inset:0; background:linear-gradient(120deg, rgba(255,255,255,.35), transparent 60%); transform:translateX(-120%); transition:.45s;}
.btn.primary:hover::after{transform:translateX(120%)}
.btn.ghost{background:rgba(255,255,255,.08); color:#e6edf6; border:1px solid rgba(255,255,255,.18)}
.btn.outline{background:transparent; color:#cfe8ff; border:1px solid rgba(99,102,241,.35)}
.btn.outline:hover{box-shadow:0 10px 24px rgba(99,102,241,.25)}
.btn:hover{transform:translateY(-2px)}

/* badges & divider */
.badges{display:flex; gap:10px; flex-wrap:wrap; margin:16px 0;}
.badge{padding:6px 12px; font-size:12px; border-radius:12px; background:rgba(255,255,255,.09); border:1px solid rgba(255,255,255,.18); backdrop-filter:blur(10px); color:#cceaff; font-weight:600; box-shadow:0 0 10px rgba(56,189,248,.25)}
.divider{height:2px; margin:30px 0 20px; background:linear-gradient(90deg,#22d3ee,#3b82f6,#a855f7); border-radius:4px; opacity:.4}

/* search bar */
.toolbar{display:flex; gap:10px; flex-wrap:wrap}
.search{
  flex:1; display:flex; align-items:center; gap:10px;
  background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.18);
  border-radius:14px; padding:12px 14px;
  transition: border-color .3s, box-shadow .3s;
}
.search:focus-within {
  border-color: rgba(96, 165, 250, .7);
  box-shadow: 0 0 20px rgba(96, 165, 250, .25);
}
.search input{flex:1; border:0; outline:none; background:transparent; color:#fff; font-size:16px}
.search input::placeholder{color:#8a9bb5}

/* sections & cards */
.section{margin-top:32px}

/* --- Kicker (Headings) --- */
.kicker{
  font-size: 14px;
  letter-spacing: .2em;
  text-transform:uppercase;
  font-weight:700;
  color:#9fb0cc;
  margin:0 10px 10px
}

/* Card grid styles */
.grid{display:grid; gap:14px; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); margin-top: 20px;}
.card{padding:14px; border-radius:14px; background:linear-gradient(180deg, rgba(255,255,255,.12), rgba(255,255,255,.06)); border:1px solid rgba(255,255,255,.18); backdrop-filter:blur(12px); transform-style:preserve-3d; transition:.25s transform,.25s background,.25s box-shadow;}
.card:hover{transform:translateY(-6px) perspective(800px) rotateX(3deg) rotateY(-3deg); background:rgba(255,255,255,.18); box-shadow:0 30px 60px rgba(2,8,23,.35), 0 0 0 1px rgba(99,102,241,.25) inset;}
.place{font-weight:900; font-size:18px; background:linear-gradient(90deg,#93c5fd,#6ee7b7); -webkit-background-clip:text; color:transparent;}
.meta{font-size:12px; opacity:.9; color:#c9d4e8}

/* Toast Notification (Unchanged) */
#toast {
  position: fixed;
  bottom: -100px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 9999;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 20px;
  border-radius: 12px;
  background: linear-gradient(120deg, #1f2937, #111827);
  color: #e6edf6;
  border: 1px solid rgba(255, 255, 255, .2);
  box-shadow: 0 10px 40px rgba(0,0,0,.5);
  backdrop-filter: blur(10px);
  transition: bottom .5s ease-in-out;
  font-weight: 600;
  font-size: 14px;
}
#toast.show { bottom: 25px; }
#toast-icon { font-size: 18px; }

</style>
</head>
<body>

<div class="aurora"></div>
<div class="noise"></div>
<div class="blob"></div>
<div class="blob"></div>
<div class="pulse"></div>
<canvas id="bg3d"></canvas>
<div class="vignette"></div>
<div class="scan"></div>

<div class="top">
  <div class="brand"><div class="mark"></div> WeatherNow</div>
</div>


<div class="page-container">

  <div class="content-box slide-in" id="welcome-box">
    <div class="title">🌤️ Welcome</div>
    <div class="subtitle" id="tagline">Type a city to view the forecast.</div>

    <div class="ctas">
      <button type="button" class="btn outline" onclick="document.getElementById('city').value='Kolkata'">Kolkata</button>
      <button type="button" class="btn outline" onclick="document.getElementById('city').value='Delhi'">Delhi</button>
      <button type="button" class="btn outline" onclick="document.getElementById('city').value='London'">London</button>
    </div>

    <div class="divider"></div>

    <form class="toolbar" method="post" action="welcome.php" onsubmit="return go(this.city.value)">
      <div class="search">
        <span>🔎</span>
        <input id="city" name="city" placeholder="Search any city or postal code…" autofocus value="<?= h(isset($_POST['city']) ? $_POST['city'] : '') ?>">
      </div>
      <button type="button" class="btn ghost" onclick="useMyLocation()">Use my location</button>
      <button type="submit" class="btn primary">Search</button>
    </form>
  </div>


  <div class="content-box slide-in" id="about-box">
    <div class="kicker">About Us</div>
    <div style="line-height:1.6; font-size: 17px; color: #c9d4e8;">
      WeatherNow is a next-gen real-time weather intelligence experience built on open climate
      APIs, smart geocoding, location detection and precision forecasting — fast, private, no keys required.
    </div>

    <div class="badges" style="margin-top: 24px;">
      <div class="badge">Real-time</div>
      <div class="badge">Hour-by-hour</div>
      <div class="badge">Global</div>
      <div class="badge">PIN Enabled</div>
      <div class="badge">Reliable</div>
    </div>
  </div>


  <div class="content-box slide-in" id="features-box">
    <div class="kicker">Features</div>
    <div class="grid">
      <div class="card"><div class="place">🌍 Global Forecast</div><div class="meta">Worldwide coverage.</div></div>
      <div class="card"><div class="place">📍 GPS Smart</div><div class="meta">Location auto-detect.</div></div>
      <div class="card"><div class="place">⚡ Silent PIN Search</div><div class="meta">Works behind UI.</div></div>
      <div class="card"><div class="place">🧠 Accurate Models</div><div class="meta">High precision data.</div></div>
      <div class="card"><div class="place">⏱️ Hourly Data</div><div class="meta">Next 24h insights.</div></div>
      <div class="card"><div class="place">📈 Weekly Trend</div><div class="meta">7-day forecast.</div></div>
    </div>
  </div>


  <div class="content-box slide-in" id="recent-box">
    <div class="kicker">Recent Locations</div>
    <div id="recentGrid" class="grid">
          </div>
    <div id="noRecent" class="meta" style="text-align:center;margin-top:10px; display:none;">
      Start searching to save your recent cities.
    </div>
  </div>
  
  
  <div class="content-box slide-in" id="developer-box" style="padding: 24px 26px;">
    <div class="kicker">Developer</div>
    
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
        <div style="line-height:1.6; font-size: 18px; color: #c9d4e8; font-weight: 600;">
          Developed by <span style="background:linear-gradient(90deg,#60a5fa,#34d399); -webkit-background-clip:text; color:transparent; font-weight: 900;">Rupam</span>
        </div>
        <div class="badge" style="background-color: rgba(96, 165, 250, 0.2); border-color: rgba(96, 165, 250, 0.4);">
          WeatherNow v1.0
        </div>
    </div>
  </div>
  </div> <div id="toast">
  <div id="toast-icon"></div>
  <div id="toast-msg"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.160.0/examples/js/controls/OrbitControls.js"></script>

<script>
/* 3D background (Unchanged) */
(function init3D(){
  try{
    if(typeof THREE==='undefined') throw new Error('Three.js missing');
    const canvas=document.getElementById('bg3d');
    const renderer=new THREE.WebGLRenderer({canvas,antialias:true});
    renderer.setPixelRatio(Math.min(window.devicePixelRatio,1.8));
    renderer.setSize(window.innerWidth,window.innerHeight);

    const scene=new THREE.Scene();
    const bg=new THREE.TextureLoader().load('https://images.unsplash.com/photo-1520975916090-3105956dac38?q=80&w=1920&auto-format&fit-crop');
    bg.colorSpace=THREE.SRGBColorSpace; scene.background=bg;

    const camera=new THREE.PerspectiveCamera(50,window.innerWidth/window.innerHeight,0.1,100);
    camera.position.set(0,0.6,2.1);
    const controls=new THREE.OrbitControls(camera,renderer.domElement);
    controls.enableZoom=false;controls.enablePan=false;controls.enableRotate=false;

    scene.add(new THREE.AmbientLight(0xffffff,.75));
    const dl=new THREE.DirectionalLight(0xffffff,1.2);dl.position.set(3,2,2);scene.add(dl);

    const globe=new THREE.Mesh(new THREE.SphereGeometry(.75,64,64),
      new THREE.MeshStandardMaterial({color:0x5fa8ff,roughness:.35,metalness:.08,emissive:0x22d3ee,emissiveIntensity:.06})
    );scene.add(globe);

    const halo=new THREE.Mesh(new THREE.SphereGeometry(.82,64,64),
      new THREE.MeshBasicMaterial({color:0x7dd3fc,opacity:.22,transparent:true})
    );scene.add(halo);

    const geo=new THREE.BufferGeometry();const N=240;const pos=new Float32Array(N*3);
    for(let i=0;i<N;i++){
      const r=1.06,t=Math.random()*Math.PI*2,u=Math.acos(2*Math.random()-1);
      pos[i*3]=r*Math.sin(u)*Math.cos(t);
      pos[i*3+1]=r*Math.cos(u);
      pos[i*3+2]=r*Math.sin(u)*Math.sin(t);
    }
    geo.setAttribute('position',new THREE.BufferAttribute(pos,3));
    const dots=new THREE.Points(geo,new THREE.PointsMaterial({size:.012,color:0xffffff,opacity:.75,transparent:true}));
    scene.add(dots);

    (function animate(){
      requestAnimationFrame(animate);
      globe.rotation.y+=.001; halo.rotation.y+=.0012; dots.rotation.y-=.0007;
      renderer.render(scene,camera);
    })();

    window.addEventListener('resize',()=>{renderer.setSize(innerWidth,innerHeight); camera.aspect=innerWidth/innerHeight; camera.updateProjectionMatrix();});
  }catch(e){console.warn('[3D Disabled]',e.message);}
})();


/* Toast Notification Function (Unchanged) */
let toastTimer;
function showToast(message, icon = 'ℹ️', duration = 4000) {
  const toast = document.getElementById('toast');
  if (!toast) return;
  document.getElementById('toast-icon').textContent = icon;
  document.getElementById('toast-msg').textContent = message;

  toast.classList.add('show');

  if (toastTimer) clearTimeout(toastTimer);
  toastTimer = setTimeout(() => {
    toast.classList.remove('show');
  }, duration);
}


/* subtitle typing (Unchanged) */
const TAGS=["Type a city to view the forecast.","Try: Delhi • Kolkata • London","Use your location instantly"];
let ti=0; function cycle(){const el=document.getElementById('tagline');if(!el) return; el.textContent='';const t=TAGS[ti++%TAGS.length];[...t].forEach((c,i)=>setTimeout(()=>el.textContent+=c,18*i))} cycle(); setInterval(cycle,4200);

/* PIN support (Unchanged) */
function looksPostal(q){q=q.trim(); if(/^\d{6}$/.test(q))return true; if(/^\d{4,10}$/.test(q))return true; if(/^[A-Za-z0-9][A-Za-z0-9 \-]{2,9}$/.test(q)&&/\d/.test(q))return true; return false;}
async function resolveQuery(q){
  q=q.trim();
  if(looksPostal(q)){
    try{
      const r=await fetch(`https://nominatim.openstreetmap.org/search?postalcode=${encodeURIComponent(q)}&format=json&addressdetails=1&limit=1`);
      const j=await r.json();
      if(j.length){
        const a=j[0].address; const city=a.city||a.town||a.village||a.suburb||""; const state=a.state||"";
        const label = city&&state ? `${city}, ${state}` : (city||state);
        if(label) return label;
      }
    }catch(e){ console.error("Postal code lookup failed:", e); }
  }
  return q;
}

/* Recents (Unchanged) */
const KEY='wx_recent';
function getRecent(){try{return JSON.parse(localStorage.getItem(KEY)||'[]')}catch(e){console.error("Error reading recent locations:", e); return[];}}
function saveRecent(x){
  if (!x || typeof x !== 'string' || x.trim().length === 0) return;
  const lowerX = x.trim().toLowerCase();
  let a = getRecent();
  a = a.filter(v => v.trim().toLowerCase() !== lowerX);
  a.unshift(x.trim());
  try {
      localStorage.setItem(KEY, JSON.stringify(a.slice(0, 8)));
  } catch (e) {
      console.error("Error saving recent locations:", e);
  }
}
function renderRecent(){
  const grid=document.getElementById('recentGrid'), empty=document.getElementById('noRecent'), arr=getRecent();
  if(!grid || !empty) {
      console.warn("Recent locations grid or placeholder not found.");
      return;
  }
  grid.innerHTML='';
  if(!arr.length){
      empty.style.display='block';
      grid.style.display = 'none';
      return;
  }
  empty.style.display='none';
  grid.style.display = 'grid';
  arr.forEach((n,i)=>{
      const el=document.createElement('div');
      el.className='card';
      el.innerHTML=`<div class="place">${n}</div><div class="meta">#${i+1}</div>`;
      el.onclick=()=>go(n);
      grid.appendChild(el);
  });
}


/* Actions (Unchanged) */
async function go(city){
  city=(city||'').trim();
  if(!city) {
      showToast("Please enter a city name.", "⚠️");
      return false;
  }
  console.log("Saving city:", city);
  saveRecent(city);
  renderRecent();
  window.location.href='index.php?city='+encodeURIComponent(city);
  return false;
}

/* useMyLocation (Unchanged) */
async function useMyLocation() {
  if (!navigator.geolocation) {
    showToast("Geolocation is not supported by your browser.", "🚫");
    return;
  }
  if (!window.isSecureContext) {
     showToast("SecurityError: Location services require HTTPS.", "🔒");
     console.warn("Geolocation API blocked: Only available in secure contexts (HTTPS or localhost).");
     return;
  }
  showToast("Accessing your location...", "🌍");
  navigator.geolocation.getCurrentPosition(async pos => {
    const { latitude: lat, longitude: lon } = pos.coords;
    showToast("Location found. Fetching city...", "✅");
    let label = "My location";
    try {
      const r = await fetch(`https://geocoding-api.open-meteo.com/v1/reverse?latitude=${lat}&longitude=${lon}&format=json`);
      if (r.ok) {
          const j = await r.json();
          const c = j.results?.[0];
          if (c) {
              label = `${c.name}${c.admin1 ? ', ' + c.admin1 : ''}`;
          }
      } else {
          console.warn("Reverse geocoding failed, status:", r.status);
      }
    } catch (fetchErr) {
      console.error("Reverse geocoding API error:", fetchErr);
      showToast("Could not fetch city name, using coordinates.", "⚠️");
      label = `${lat.toFixed(4)}, ${lon.toFixed(4)}`;
    } finally {
        console.log("Saving location:", label);
        saveRecent(label);
        renderRecent();
        window.location.href = 'index.php?city=' + encodeURIComponent(label);
    }
  }, err => {
    let msg = "Could not get location.";
    if (err.code === 1) msg = "Permission denied. Please allow location access.";
    else if (err.code === 2) msg = "Location position unavailable.";
    else if (err.code === 3) msg = "Location request timed out.";
    showToast(msg, "🚫");
    console.error("Geolocation error:", err.message);
  }, {
    enableHighAccuracy: false,
    timeout: 8000,
    maximumAge: 60000
  });
}


/* Enter key to search (Unchanged) */
document.addEventListener('keydown',e=>{ if(e.key==='Enter' && document.activeElement === document.getElementById('city')){ go(document.getElementById('city').value);} });

// Initial call when the DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    renderRecent(); // Render recent locations
    
    // --- NEW: Slide-in Animation Trigger ---
    // We add 'visible' class to each box with a delay
    const boxes = document.querySelectorAll('.slide-in');
    boxes.forEach((box, index) => {
        setTimeout(() => {
            box.classList.add('visible');
        }, index * 150); // 150ms delay between each box
    });
});

/* --- Parallax tilt on cards (Unchanged) --- */
document.querySelectorAll('.content-box').forEach(box => {
  box.addEventListener('mousemove', (e) => {
    box.querySelectorAll('.card').forEach(card => {
        const r = card.getBoundingClientRect();
        const cx = e.clientX - (r.left + r.width/2);
        const cy = e.clientY - (r.top  + r.height/2);
        const rx = (+cy / r.height) * 4;
        const ry = (-cx / r.width)  * 4;

        if (!card.matches(':hover')) {
          card.style.transform = `perspective(800px) rotateX(${rx}deg) rotateY(${ry}deg)`;
        }
    });
  });
  box.addEventListener('mouseleave', () => {
      box.querySelectorAll('.card').forEach(card => {
          card.style.transform = '';
      });
  });
});

document.querySelectorAll('.card').forEach(c=>c.addEventListener('mouseleave',()=>{
    c.style.transform='';
}));

</script>

</body>
</html>