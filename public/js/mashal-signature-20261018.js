
/* Signature Navbar interaction — additive to existing menu/search behavior. */
document.addEventListener('DOMContentLoaded',function(){
 const header=document.getElementById('studioHeader');
 if(!header)return;
 const reduce=window.matchMedia('(prefers-reduced-motion: reduce)');
 if(!reduce.matches&&window.matchMedia('(pointer:fine)').matches){
   let raf=0;
   header.addEventListener('pointermove',function(ev){
     if(raf)return;
     const x=ev.clientX-header.getBoundingClientRect().left;
     raf=requestAnimationFrame(function(){
       header.style.setProperty('--signature-pointer-x',x+'px');raf=0
     });
   },{passive:true});
 }
 const nav=header.querySelector('.expert-nav-center');
 if(!nav)return;
 const links=Array.from(nav.querySelectorAll('.expert-nav-link'));
 if(!links.length)return;
 const highlight=document.createElement('span');
 highlight.className='signature-nav-indicator';
 highlight.setAttribute('aria-hidden','true');
 nav.prepend(highlight);
 const active=nav.querySelector('.expert-nav-link.active')||links[0];
 let timer=0;
 function place(link){
   if(!link||!link.isConnected||nav.getClientRects().length===0)return;
   const rect=link.getBoundingClientRect(),parent=nav.getBoundingClientRect();
   highlight.style.width=rect.width+'px';
   highlight.style.height=rect.height+'px';
   highlight.style.transform='translate3d('+(rect.left-parent.left)+'px,'+(rect.top-parent.top)+'px,0)';
   highlight.classList.add('is-visible')
 }
 links.forEach(function(link){
   link.addEventListener('pointerenter',function(){clearTimeout(timer);place(link)});
   link.addEventListener('focus',function(){place(link)});
   link.addEventListener('blur',function(){timer=setTimeout(function(){place(active)},75)})
 });
 nav.addEventListener('pointerleave',function(){clearTimeout(timer);timer=setTimeout(function(){place(active)},80)});
 place(active);
 window.addEventListener('resize',function(){place(active)},{passive:true});
 if('ResizeObserver' in window){new ResizeObserver(function(){place(active)}).observe(nav)}
 if(document.fonts&&document.fonts.ready)document.fonts.ready.then(function(){place(active)});
});
