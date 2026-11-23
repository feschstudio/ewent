(function(){
  var S=window.EWENT_SINGLE,E=window.EWENT||{};if(!S)return;
  var $=function(s){return document.querySelector(s)};
  function esc(s){return String(s).replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]})}
  function post(route,data){return fetch((E.rest||'/wp-json/ewent/v1/')+route,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)}).then(function(r){return r.json().catch(function(){return {}}).then(function(j){if(!r.ok)throw new Error(j&&j.message||'Das hat leider nicht geklappt.');return j})})}
  var sh=$('#d_share');if(sh)sh.onclick=function(){var b=this,u=location.href;if(navigator.share){navigator.share({title:S.title,url:u}).catch(function(){})}else{try{navigator.clipboard.writeText(S.title+' '+u)}catch(x){}b.textContent='Link kopiert';setTimeout(function(){b.textContent='Teilen'},1800)}};
  var cal=$('#d_cal');if(cal)cal.onclick=function(){
    var today=new Date().toISOString().slice(0,10),d=S.dates.filter(function(x){return x[0]>=today})[0]||S.dates[0];if(!d)return;
    var p=function(n){return (n<10?'0':'')+n},h=(d[1]||'00:00').split(':'),ymd=d[0].replace(/-/g,''),st=ymd+'T'+h[0]+h[1]+'00',en=ymd+'T'+p((+h[0]+2)%24)+h[1]+'00';
    var t=['BEGIN:VCALENDAR','VERSION:2.0','PRODID:-//ewent.//DE','BEGIN:VEVENT','UID:'+S.id+'-'+ymd+'@ewent.at','DTSTAMP:'+ymd+'T000000','DTSTART:'+st,'DTEND:'+en,'SUMMARY:'+S.title,'LOCATION:'+S.place+', '+S.town,'URL:'+location.href,'END:VEVENT','END:VCALENDAR'].join('\r\n');
    var a=document.createElement('a');a.href=URL.createObjectURL(new Blob([t],{type:'text/calendar'}));a.download='event.ics';document.body.appendChild(a);a.click();a.remove()};
  var cb=$('#d_claim');if(cb)cb.onclick=function(){var w=$('#claimbox');w.innerHTML='<b>Event übernehmen</b><div class="field" style="margin-top:12px"><label for="c_mail">Deine E-Mail</label><input class="in" id="c_mail" type="email" inputmode="email" autocomplete="email" placeholder="name@veranstalter.at"></div><p class="note" id="c_err" style="color:#A3402A;display:none" role="alert">Bitte eine gültige E-Mail eingeben.</p><button class="btn accent block" id="c_send" type="button">Übernahme anfragen</button>';
    $('#c_send').onclick=function(){var m=$('#c_mail').value.trim();if(!/^\S+@\S+\.\S+$/.test(m)){$('#c_err').style.display='block';return}var sb=this;sb.disabled=true;post('claim',{event:S.id,mail:m}).then(function(){w.innerHTML='<b>Danke, deine Anfrage ist raus.</b><p class="note" style="margin:8px 0 0">Wir prüfen kurz, ob das Event zu dir gehört, und melden uns bei '+esc(m)+'.</p>'}).catch(function(er){sb.disabled=false;var c=$('#c_err');c.textContent=er.message;c.style.display='block'})}};
})();
