@extends('layouts.site-layout')

@section('title', 'Mashal Studio Text Editor')
@section('meta_description', 'Bewerk tekst op afbeeldingen in de eigen Mashal Studio-editor.')

@push('styles')
<style>
.studio-editor-page{min-height:calc(100vh - 70px);padding:28px 0 54px;color:#f5f4fb;background:radial-gradient(ellipse at 12% 0%,rgba(143,130,255,.13),transparent 35%),linear-gradient(180deg,#08090d,#0b0d13 55%,#08090d)}
.studio-editor-shell{width:min(100% - 32px,1500px);margin:auto}
.studio-editor-head{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:20px}
.studio-editor-brand{display:flex;align-items:center;gap:12px}.studio-editor-mark{width:42px;height:42px;display:grid;place-items:center;border-radius:13px;background:linear-gradient(135deg,#a99fff,#5f7dff);font-weight:950;color:#fff;box-shadow:0 8px 28px #766aff35}
.studio-editor-brand small{display:block;color:#858b99;font-size:10px;letter-spacing:.16em;text-transform:uppercase}.studio-editor-brand strong{font-size:17px;letter-spacing:-.03em}
.studio-editor-head-actions{display:flex;gap:8px;flex-wrap:wrap}
.se-btn{border:1px solid #ffffff18;background:#151821;color:#e9e8f2;border-radius:10px;padding:10px 13px;font:700 11px inherit;cursor:pointer;transition:.18s}
.se-btn:hover{border-color:#a99fff88;background:#202333;transform:translateY(-1px)}.se-btn.primary{border:0;background:linear-gradient(120deg,#8f82ff,#5f7dff);color:white;box-shadow:0 8px 25px #7166ff30}.se-btn:disabled{opacity:.4;cursor:not-allowed;transform:none}
.studio-editor-title{margin:0 0 17px}.studio-editor-title .eyebrow{color:#a99fff;font-size:9px;font-weight:900;letter-spacing:.2em;text-transform:uppercase}.studio-editor-title h1{font-size:clamp(25px,3vw,38px);letter-spacing:-.055em;margin:7px 0}.studio-editor-title p{margin:0;color:#89909d;font-size:12px}
.se-workspace{display:grid;grid-template-columns:220px minmax(0,1fr) 250px;gap:12px;align-items:stretch}
.se-panel{border:1px solid #ffffff12;border-radius:16px;background:linear-gradient(145deg,#141720f5,#0f1219f5);box-shadow:0 18px 50px #0004;min-width:0}
.se-panel-head{padding:15px 15px 12px;border-bottom:1px solid #ffffff10;color:#e9e8f2;font-size:10px;font-weight:900;letter-spacing:.13em;text-transform:uppercase}
.se-tools{padding:12px;display:grid;grid-template-columns:1fr 1fr;gap:8px}
.se-tool{min-height:72px;border:1px solid #ffffff10;border-radius:11px;background:#ffffff05;color:#c9cbd5;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:7px;font-size:10px;font-weight:750;cursor:pointer}.se-tool span{font-size:20px;color:#a99fff}.se-tool:hover{border-color:#a99fff77;background:#a99fff12;color:#fff}
.se-tool.wide{grid-column:1/-1;min-height:48px;flex-direction:row}
.se-field{padding:12px 14px 0}.se-field label{display:block;margin-bottom:7px;color:#9298a5;font-size:9px;font-weight:850;letter-spacing:.1em;text-transform:uppercase}.se-field input:not([type=color]),.se-field select{width:100%;height:37px;padding:0 10px;border:1px solid #ffffff15;border-radius:9px;background:#0c0f16;color:#f0eff8;font-size:11px;outline:none}.se-field input:focus,.se-field select:focus{border-color:#a99fff}
.se-field input[type=color]{width:100%;height:37px;padding:3px;border:1px solid #ffffff18;border-radius:9px;background:#0c0f16}
.se-inline{display:grid;grid-template-columns:1fr 1fr;gap:8px}.se-toggles{display:flex;gap:6px}.se-toggle{flex:1;height:35px;border:1px solid #ffffff15;border-radius:8px;background:#ffffff05;color:#c9cbd5;font-weight:800;cursor:pointer}.se-toggle.active{border-color:#a99fff88;background:#a99fff20;color:#fff}
.se-canvas-panel{display:flex;flex-direction:column;min-height:660px;overflow:hidden}.se-canvas-top{min-height:48px;padding:9px 13px;display:flex;align-items:center;justify-content:space-between;gap:10px;border-bottom:1px solid #ffffff10;color:#969dab;font-size:10px}.se-canvas-top strong{color:#e8e7f0}.se-stage{position:relative;flex:1;min-height:570px;display:flex;align-items:center;justify-content:center;padding:28px;overflow:auto;background-color:#0b0e14;background-image:linear-gradient(45deg,#ffffff08 25%,transparent 25%),linear-gradient(-45deg,#ffffff08 25%,transparent 25%),linear-gradient(45deg,transparent 75%,#ffffff08 75%),linear-gradient(-45deg,transparent 75%,#ffffff08 75%);background-size:22px 22px;background-position:0 0,0 11px,11px -11px,-11px 0}
.se-canvas-wrap{max-width:100%;box-shadow:0 20px 70px #0009;background:#fff}.se-canvas-wrap canvas{display:block;max-width:100%;height:auto}
.se-empty{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;text-align:center;padding:24px;pointer-events:none}.se-empty-inner{max-width:320px}.se-empty-icon{width:66px;height:66px;margin:0 auto 15px;display:grid;place-items:center;border:1px solid #a99fff55;border-radius:20px;background:#a99fff12;color:#b7afff;font-size:27px}.se-empty h3{margin:0 0 8px;color:#f0eff8;font-size:17px}.se-empty p{margin:0;color:#858d9b;font-size:11px;line-height:1.7}
.se-status{padding:10px 14px;border-top:1px solid #ffffff10;color:#858d9b;font-size:10px}.se-layer-list{padding:10px}.se-layer{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:9px 10px;border:1px solid #ffffff0c;border-radius:9px;background:#ffffff04;color:#c9cbd5;font-size:10px;margin-bottom:6px;cursor:pointer}.se-layer.active{border-color:#a99fff70;background:#a99fff12;color:#fff}.se-layer small{color:#777f8d}
.se-hint{padding:12px 14px;color:#777f8d;font-size:9px;line-height:1.65}
.se-mobile-tabs{display:none}
@media(max-width:1050px){.se-workspace{grid-template-columns:190px minmax(0,1fr)}.se-properties{grid-column:1/-1;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));align-items:start}.se-properties .se-panel-head{grid-column:1/-1}.se-properties .se-field{padding-bottom:12px}.se-properties .se-layer-list{grid-column:1/-1}}
@media(max-width:700px){.studio-editor-shell{width:min(100% - 20px,1500px)}.studio-editor-head{align-items:flex-start}.studio-editor-brand strong{font-size:14px}.studio-editor-head-actions .se-btn{padding:9px}.se-workspace{display:flex;flex-direction:column}.se-tools{grid-template-columns:repeat(4,1fr)}.se-tool{min-height:62px;font-size:9px}.se-tool.wide{grid-column:auto;flex-direction:column}.se-canvas-panel{min-height:470px;order:2}.se-stage{min-height:390px;padding:12px}.se-properties{display:block;order:3}.se-properties .se-field{padding-bottom:12px}.se-left-tools{order:1}.se-canvas-top{font-size:9px}.studio-editor-title p{line-height:1.6}}
</style>
@endpush

@section('content')
<main class="studio-editor-page">
<div class="studio-editor-shell">
<header class="studio-editor-head">
 <div class="studio-editor-brand"><span class="studio-editor-mark">M</span><span><small>Mashal Studio · Creative workspace</small><strong>Text Image Editor</strong></span></div>
 <div class="studio-editor-head-actions"><a class="se-btn" href="{{ route('images.index') }}" style="text-decoration:none">← Afbeeldingen</a><button class="se-btn" id="se-undo" type="button" disabled>↶ Ongedaan</button><button class="se-btn" id="se-redo" type="button" disabled>↷ Opnieuw</button><button class="se-btn primary" id="se-export" type="button">Download ontwerp ↓</button></div>
</header>
<div class="studio-editor-title"><div class="eyebrow">Your creative tools · Your style</div><h1>Maak je afbeelding <span style="color:#a99fff">eigen.</span></h1><p>Voeg tekst toe, pas de typografie aan en exporteer je ontwerp. Alles in de Mashal Studio-werkruimte.</p></div>
<div class="se-workspace">
 <aside class="se-panel se-left-tools">
  <div class="se-panel-head">Tools & elementen</div>
  <div class="se-tools">
   <button class="se-tool wide" id="se-upload"><span>＋</span>Afbeelding openen</button>
   <button class="se-tool" id="se-add-text"><span>T</span>Tekst</button>
   <button class="se-tool" id="se-add-heading"><span>H</span>Koptekst</button>
   <button class="se-tool" id="se-add-subtitle"><span>≡</span>Subtitel</button>
   <button class="se-tool" id="se-add-rect"><span>▰</span>Vlak</button>
   <button class="se-tool" id="se-add-circle"><span>●</span>Cirkel</button>
   <button class="se-tool" id="se-delete"><span>⌫</span>Verwijderen</button>
   <button class="se-tool" id="se-front"><span>⇧</span>Naar voren</button>
   <button class="se-tool" id="se-back"><span>⇩</span>Naar achter</button>
  </div>
  <input type="file" id="se-file" accept="image/png,image/jpeg,image/webp" hidden>
  <div class="se-hint">Tip: selecteer een tekstlaag op het canvas om lettertype, grootte, kleur en stijl aan te passen.</div>
 </aside>
 <section class="se-panel se-canvas-panel">
  <div class="se-canvas-top"><span><strong>Canvas</strong>　·　Vrij bewerken</span><span id="se-dimensions">Geen afbeelding geopend</span></div>
  <div class="se-stage" id="se-stage">
   <div class="se-canvas-wrap" id="se-canvas-wrap" hidden><canvas id="se-canvas"></canvas></div>
   <div class="se-empty" id="se-empty"><div class="se-empty-inner"><div class="se-empty-icon">✦</div><h3>Begin met je ontwerp</h3><p>Open een afbeelding vanaf je apparaat of voeg eerst een tekst- of vormlaag toe.</p><button class="se-btn primary" id="se-empty-upload" type="button" style="margin-top:16px;pointer-events:auto">Afbeelding uploaden</button></div></div>
  </div>
  <div class="se-status" id="se-status" role="status" aria-live="polite">Klaar om te ontwerpen · Je bestanden blijven op je apparaat totdat je ze exporteert.</div>
 </section>
 <aside class="se-panel se-properties">
  <div class="se-panel-head">Eigenschappen</div>
  <div class="se-field"><label for="se-text">Tekstinhoud</label><input id="se-text" type="text" placeholder="Selecteer een tekstlaag" disabled></div>
  <div class="se-field"><label for="se-font">Lettertype</label><select id="se-font" disabled><option>Arial</option><option>Inter</option><option>Georgia</option><option>Impact</option><option>Verdana</option><option>Times New Roman</option><option>Courier New</option><option>Trebuchet MS</option></select></div>
  <div class="se-field se-inline"><div><label for="se-size">Grootte</label><input id="se-size" type="number" min="6" max="300" value="48" disabled></div><div><label for="se-color">Tekstkleur</label><input id="se-color" type="color" value="#ffffff" disabled></div></div>
  <div class="se-field"><label>Tekststijl</label><div class="se-toggles"><button class="se-toggle" id="se-bold" type="button" disabled>B</button><button class="se-toggle" id="se-italic" type="button" disabled><i>I</i></button><button class="se-toggle" id="se-underline" type="button" disabled><u>U</u></button></div></div>
  <div class="se-field"><label for="se-align">Uitlijning</label><select id="se-align" disabled><option value="left">Links</option><option value="center">Midden</option><option value="right">Rechts</option></select></div>
  <div class="se-field"><label for="se-opacity">Doorzichtigheid</label><input id="se-opacity" type="range" min="10" max="100" value="100" disabled style="width:100%;accent-color:#a99fff"></div>
  <div class="se-field"><label>Laagvolgorde</label><div class="se-toggles"><button class="se-toggle" id="se-layer-up" type="button" disabled>Omhoog</button><button class="se-toggle" id="se-layer-down" type="button" disabled>Omlaag</button></div></div>
  <div class="se-panel-head" style="margin-top:14px">Lagen</div><div class="se-layer-list" id="se-layers"><div class="se-hint">Nog geen lagen</div></div>
 </aside>
</div>
</div>
</main>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/6.7.1/index.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
(() => {
'use strict';
const $=id=>document.getElementById(id);
const status=$('se-status'), file=$('se-file'), wrap=$('se-canvas-wrap'), empty=$('se-empty');
let canvas=null,history=[],historyIndex=-1,restoring=false,selected=null;
const say=t=>status.textContent=t;
function init(){if(!window.fabric){say('De editorbibliotheek kon niet worden geladen. Vernieuw de pagina en probeer opnieuw.');return;}
canvas=new fabric.Canvas('se-canvas',{backgroundColor:'#ffffff',preserveObjectStacking:true,selection:true});
canvas.setDimensions({width:900,height:600});
canvas.on('selection:created',sync);canvas.on('selection:updated',sync);canvas.on('selection:cleared',sync);
canvas.on('object:modified',snapshot);canvas.on('object:added',()=>{if(!restoring) snapshot();});canvas.on('object:removed',()=>{if(!restoring)snapshot();});
snapshot();resizeCanvas();
}
function resizeCanvas(){if(!canvas)return;const stage=$('se-stage');const max=Math.max(250,stage.clientWidth-56);const scale=Math.min(1,max/canvas.getWidth());canvas.setZoom(scale);canvas.requestRenderAll();}
function snapshot(){if(!canvas||restoring)return;const json=JSON.stringify(canvas.toJSON());if(history[historyIndex]===json)return;history=history.slice(0,historyIndex+1);history.push(json);if(history.length>40)history.shift();historyIndex=history.length-1;updateHistoryButtons();renderLayers();}
function updateHistoryButtons(){$('se-undo').disabled=historyIndex<=0;$('se-redo').disabled=historyIndex>=history.length-1;}
function restore(index){if(index<0||index>=history.length)return;historyIndex=index;restoring=true;canvas.loadFromJSON(JSON.parse(history[index])).then(()=>{restoring=false;canvas.requestRenderAll();sync();renderLayers();updateHistoryButtons();});}
function add(obj){canvas.add(obj);canvas.setActiveObject(obj);canvas.requestRenderAll();sync();snapshot();}
function addText(text,size=48){if(!canvas){init();}const t=new fabric.IText(text,{left:canvas.getWidth()/2,top:canvas.getHeight()/2,originX:'center',originY:'center',fontFamily:'Arial',fontSize:size,fill:'#ffffff',fontWeight:'bold',shadow:new fabric.Shadow({color:'rgba(0,0,0,.45)',blur:3,offsetX:1,offsetY:2})});add(t);say('Tekstlaag toegevoegd. Pas de instellingen rechts aan.');}
function active(){return canvas&&canvas.getActiveObject();}
function sync(){const o=active(),isText=o&&(o.type==='i-text'||o.type==='textbox'||o.type==='text');selected=o||null;['se-text','se-font','se-size','se-color','se-bold','se-italic','se-underline','se-align','se-opacity','se-layer-up','se-layer-down'].forEach(id=>$(id).disabled=!isText && !['se-opacity','se-layer-up','se-layer-down'].includes(id)?true:!o);
$('se-text').value=isText?o.text:'';$('se-font').value=isText?o.fontFamily||'Arial':'Arial';$('se-size').value=isText?Math.round(o.fontSize||48):48;$('se-color').value=isText&&typeof o.fill==='string'&&/^#[0-9a-f]{6}$/i.test(o.fill)?o.fill:'#ffffff';$('se-align').value=isText?o.textAlign||'left':'left';$('se-opacity').value=o?Math.round((o.opacity??1)*100):100;
$('se-bold').classList.toggle('active',!!(isText&&o.fontWeight==='bold'));$('se-italic').classList.toggle('active',!!(isText&&o.fontStyle==='italic'));$('se-underline').classList.toggle('active',!!(isText&&o.underline));renderLayers();}
function change(prop,value){const o=active();if(!o)return;o.set(prop,value);canvas.requestRenderAll();snapshot();sync();}
function renderLayers(){if(!canvas)return;const host=$('se-layers'),objs=canvas.getObjects().slice().reverse();host.innerHTML='';if(!objs.length){host.innerHTML='<div class="se-hint">Nog geen lagen</div>';return;}objs.forEach((o,i)=>{const b=document.createElement('button');b.type='button';b.className='se-layer'+(o===active()?' active':'');const label=o.type==='image'?'Afbeelding':o.type==='rect'?'Vlak':o.type==='circle'?'Cirkel':(o.text||'Tekstlaag').slice(0,22);b.innerHTML='<span></span><small>Laag '+(objs.length-i)+'</small>';b.querySelector('span').textContent=label;b.addEventListener('click',()=>{canvas.setActiveObject(o);canvas.requestRenderAll();sync();});host.appendChild(b);});}
function loadImage(f){if(!f)return;if(!/^image\/(png|jpeg|webp)$/.test(f.type)){say('Gebruik een PNG-, JPG- of WEBP-afbeelding.');return;}if(f.size>20*1024*1024){say('Maximale bestandsgrootte is 20 MB.');return;}const url=URL.createObjectURL(f);fabric.FabricImage.fromURL(url).then(img=>{const maxW=1400,maxH=1000,scale=Math.min(1,maxW/img.width,maxH/img.height);canvas.clear();canvas.setBackgroundColor('#ffffff',canvas.renderAll.bind(canvas));canvas.setDimensions({width:Math.round(img.width*scale),height:Math.round(img.height*scale)});img.set({left:0,top:0,scaleX:scale,scaleY:scale,selectable:false,evented:false});canvas.add(img);canvas.sendObjectToBack(img);canvas.discardActiveObject();canvas.setZoom(1);canvas.requestRenderAll();wrap.hidden=false;empty.hidden=true;$('se-dimensions').textContent=canvas.getWidth()+' × '+canvas.getHeight();say('Afbeelding geopend. Voeg tekst of vormen toe en bewerk ze rechts.');history=[];historyIndex=-1;snapshot();resizeCanvas();URL.revokeObjectURL(url);}).catch(()=>{say('Afbeelding kon niet worden geopend. Probeer een ander bestand.');URL.revokeObjectURL(url);});}
$('se-upload').onclick=$('se-empty-upload').onclick=()=>file.click();file.addEventListener('change',()=>{if(file.files[0])loadImage(file.files[0]);file.value='';});
$('se-add-text').onclick=()=>addText('Jouw tekst',42);$('se-add-heading').onclick=()=>addText('JOUW KOPTEKST',64);$('se-add-subtitle').onclick=()=>addText('Voeg een subtitel toe',28);
$('se-add-rect').onclick=()=>add(new fabric.Rect({left:100,top:100,width:220,height:100,fill:'#8f82ff',opacity:.85,rx:8,ry:8}));
$('se-add-circle').onclick=()=>add(new fabric.Circle({left:130,top:130,radius:55,fill:'#a99fff',opacity:.9}));
$('se-delete').onclick=()=>{const o=active();if(o){canvas.remove(o);canvas.discardActiveObject();canvas.requestRenderAll();sync();snapshot();say('Geselecteerde laag verwijderd.');}};
$('se-front').onclick=()=>{const o=active();if(o){canvas.bringObjectToFront(o);canvas.requestRenderAll();snapshot();renderLayers();}};
$('se-back').onclick=()=>{const o=active();if(o){canvas.sendObjectBackwards(o);canvas.requestRenderAll();snapshot();renderLayers();}};
$('se-text').addEventListener('input',e=>change('text',e.target.value));$('se-font').onchange=e=>change('fontFamily',e.target.value);$('se-size').onchange=e=>change('fontSize',Math.max(6,Math.min(300,Number(e.target.value)||48)));$('se-color').oninput=e=>change('fill',e.target.value);$('se-align').onchange=e=>change('textAlign',e.target.value);$('se-opacity').oninput=e=>change('opacity',Number(e.target.value)/100);
$('se-bold').onclick=()=>change('fontWeight',active().fontWeight==='bold'?'normal':'bold');$('se-italic').onclick=()=>change('fontStyle',active().fontStyle==='italic'?'normal':'italic');$('se-underline').onclick=()=>change('underline',!active().underline);
$('se-layer-up').onclick=()=>$('se-front').click();$('se-layer-down').onclick=()=>$('se-back').click();
$('se-undo').onclick=()=>restore(historyIndex-1);$('se-redo').onclick=()=>restore(historyIndex+1);
$('se-export').onclick=()=>{if(!canvas||!canvas.getObjects().length){say('Open eerst een afbeelding of voeg een element toe.');return;}canvas.discardActiveObject();canvas.setZoom(1);canvas.setDimensions({width:canvas.getWidth(),height:canvas.getHeight()});canvas.requestRenderAll();const a=document.createElement('a');a.download='mashal-studio-design.png';a.href=canvas.toDataURL({format:'png',multiplier:2});a.click();say('Je ontwerp is geëxporteerd als PNG.');};
window.addEventListener('resize',()=>{if(canvas)resizeCanvas();});
init();
})();
</script>
@endsection
