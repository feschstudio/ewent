(function(){
  var D=window.EWENT_SINGLE;if(!D)return;
  function flash(b,txt){var o=b.textContent;if(b.classList.contains('shareic')){b.classList.add('ok');setTimeout(function(){b.classList.remove('ok')},1400);return}b.textContent=txt;setTimeout(function(){b.textContent=o},1600)}
  document.querySelectorAll('[data-share]').forEach(function(b){b.onclick=function(){
    var url=location.href.split('#')[0];
    if(navigator.share){navigator.share({title:D.title,url:url}).catch(function(){});return}
    try{navigator.clipboard.writeText(D.title+' '+url)}catch(x){}
    flash(b,'Kopiert');
  }});
  function p(n){return n<10?'0'+n:''+n}
  function ics(){
    var L=['BEGIN:VCALENDAR','VERSION:2.0','PRODID:-//ewent//DE','CALSCALE:GREGORIAN'];
    (D.dates||[]).forEach(function(d,i){
      var day=String(d[0]).replace(/-/g,''),t=String(d[1]||'').split(':'),h=+t[0]||0,m=+t[1]||0;
      L.push('BEGIN:VEVENT','UID:ewent-'+D.id+'-'+i+'@ewent.at','DTSTAMP:'+new Date().toISOString().replace(/[-:]|\.\d+/g,''),
        d[1]?'DTSTART:'+day+'T'+p(h)+p(m)+'00':'DTSTART;VALUE=DATE:'+day,
        d[1]?'DURATION:PT2H':'DURATION:P1D',
        'SUMMARY:'+String(D.title).replace(/([,;])/g,'\\$1'),
        'LOCATION:'+[D.place,D.town].filter(Boolean).join(', ').replace(/([,;])/g,'\\$1'),
        'URL:'+location.href.split('#')[0],'END:VEVENT');
    });
    L.push('END:VCALENDAR');return L.join('\r\n');
  }
  var c=document.getElementById('d_cal');
  if(c)c.onclick=function(){try{var a=document.createElement('a');a.href=URL.createObjectURL(new Blob([ics()],{type:'text/calendar'}));a.download='event.ics';document.body.appendChild(a);a.click();a.remove()}catch(x){}flash(c,'Geladen')};
})();
