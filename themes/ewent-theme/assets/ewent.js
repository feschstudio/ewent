(function(){
  var CATS=['Wein','Kulinarik','Musik','Markt','Kultur','Familie','Sport'];
  var TOWNS=['Krems','Stein','Dürnstein','Spitz','Weißenkirchen','Furth bei Göttweig','Langenlois','Gobelsburg','Traismauer','Herzogenburg'];
  var PROMO=true;
  var PACKS=[
    {id:'free',name:'Gratis',price:0,live:1,desc:'Startaktion: inklusive Ticket-Link und mehrtägigen Events.',from:'Höhepunkte',items:['Eintrag in der Liste','Eigene Event-Seite','Foto und Flyer/Plakat hochladen','Teilen, Kalender und Karte','Bearbeiten im Kundenkonto']},
    {id:'plus',name:'Plus',price:19,from:'Alles aus Gratis',items:['Ticket-Button zu deinem Ticket- oder Anmelde-Link','Mehrtägige Events']},
    {id:'top',name:'Top',price:49,hot:1,from:'Alles aus Plus',items:['Hervorgehoben in der Liste','Top-Platzierung Startseite']},
    {id:'premium',name:'Premium',price:89,from:'Alles aus Top',items:['Newsletter-Erwähnung','Social-Post von ewent.','Wir melden uns persönlich und besprechen die Inhalte mit dir']}
  ];



  function pad(n){return (n<10?'0':'')+n}
  function dd(s){var p=s.split('-');return new Date(+p[0],p[1]-1,+p[2])}
  var DOW=['So','Mo','Di','Mi','Do','Fr','Sa'],MON=['Jän','Feb','Mär','Apr','Mai','Jun','Jul','Aug','Sep','Okt','Nov','Dez'];
  var DOWL=['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'];
  var base=new Date();base.setHours(0,0,0,0);
  var RAW=(window.EWENT&&window.EWENT.events)||[];
  var EV=RAW.map(function(r){var p=r.date.split('-'),d=new Date(+p[0],+p[1]-1,+p[2]);return {id:r.id,url:r.url,ds:r.date,t:r.time||'',title:r.title,place:r.place||'',town:r.town||'',cat:r.cat||'',price:r.price||'Gratis',tier:r.tier||'free',org:r.org||'',credit:r.credit||'',desc:r.excerpt||'',link:r.link||'',tend:r.tend||'',image:r.image||'',thumb:r.thumb||r.image||'',manual:r.manual?1:0,multi:!!r.multi,dates:r.dates||[[r.date,r.time]],last:r.last,date:d,d:Math.round((d-base)/864e5)}}).filter(function(r){return r.d>=0}).sort(function(a,b){return a.d-b.d||a.t.localeCompare(b.t)});
  EV.forEach(function(e,i){e.i=i;e.long=e.multi&&(function(){var u={};e.dates.forEach(function(x){u[x[0]]=1});return Object.keys(u).length>=3})();e.hay=(e.title+' '+e.place+' '+e.town+' '+e.org+' '+e.cat+' '+e.desc).toLowerCase()});

  var F={q:'',time:'all',date:'',cats:[],towns:[],free:false};
  var $=function(s){return document.querySelector(s)};
  var MORE=false,C=(window.EWENT&&window.EWENT.config)||{},S={banner:!!C.btxt};
  function thumb(e){return e.image?'<img src="'+esc(e.thumb||e.image)+'" alt="" loading="lazy" decoding="async">':art(e)}
  function post(route,data){return fetch(((window.EWENT&&window.EWENT.rest)||'/wp-json/ewent/v1/')+route,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)}).then(function(r){return r.json().catch(function(){return {}}).then(function(j){if(!r.ok)throw new Error(j&&j.message||'Das hat leider nicht geklappt. Bitte versuch es nochmal.');return j})})}
  function esc(s){return String(s).replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]})}
  function days(e){return Math.round((e.date-base)/864e5)}
  function lbl(d){var n=Math.round((d-base)/864e5);var s=DOWL[d.getDay()]+', '+d.getDate()+'. '+MON[d.getMonth()];if(n===0)return 'Heute, '+d.getDate()+'. '+MON[d.getMonth()];if(n===1)return 'Morgen, '+d.getDate()+'. '+MON[d.getMonth()];return s}
  function short(d){return DOW[d.getDay()]+' '+d.getDate()+'. '+MON[d.getMonth()]}
  function dmy(s){var p=s.split('-');return +p[2]+'. '+MON[+p[1]-1]}
  function pad2(n){return (n<10?'0':'')+n}
  function iso(d){return d.getFullYear()+'-'+pad2(d.getMonth()+1)+'-'+pad2(d.getDate())}

  /* Platzhalter-Bilder */
  function artOld(e){
    var v=e.i%2,id='g'+e.i+(Math.random()*1e4|0),s='';
    var W='viewBox="0 0 400 300" preserveAspectRatio="xMidYMid slice" role="img" aria-label="Platzhalter-Bild '+esc(e.cat)+'"';
    if(e.cat==='Wein'){
      var sky=v?['#F3D9C6','#E9B79B']:['#EFE0CF','#E2B29A'];
      s='<svg '+W+'><defs><linearGradient id="'+id+'" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="'+sky[0]+'"/><stop offset="1" stop-color="'+sky[1]+'"/></linearGradient></defs><rect width="400" height="300" fill="url(#'+id+')"/><circle cx="'+(v?300:110)+'" cy="96" r="34" fill="#FAF1E6" opacity=".9"/>'+
      '<path d="M0 150 Q120 110 230 140 T400 120 V300 H0Z" fill="#9A9C7E"/><path d="M0 190 Q140 150 260 180 T400 165 V300 H0Z" fill="#7F8765"/>';
      for(var k=0;k<9;k++){s+='<path d="M'+(-20+k*10)+' 300 Q'+(120+k*22)+' '+(200-k*4)+' 420 '+(190+k*12)+'" fill="none" stroke="#5E6A4B" stroke-width="3" opacity=".55"/>'}
      s+='<path d="M0 250 Q200 220 400 250 V300 H0Z" fill="#6A7451"/><rect x="40" y="188" width="26" height="22" fill="#F7EBDD"/><path d="M36 188 L53 172 L70 188Z" fill="#B96E58"/></svg>';
    }else if(e.cat==='Kulinarik'){
      s='<svg '+W+'><rect width="400" height="300" fill="'+(v?'#C9876F':'#B97862')+'"/><rect width="400" height="300" fill="#8A5444" opacity=".25"/>'+
      '<g opacity=".14" stroke="#fff"><path d="M0 60H400M0 130H400M0 200H400M0 270H400"/></g>'+
      '<circle cx="130" cy="150" r="82" fill="#FAF8F4"/><circle cx="130" cy="150" r="58" fill="none" stroke="#DDD8D0" stroke-width="3"/><circle cx="130" cy="150" r="30" fill="#D9A27F"/><circle cx="118" cy="142" r="10" fill="#9A9C7E"/><circle cx="146" cy="160" r="9" fill="#B96E58"/>'+
      '<circle cx="292" cy="100" r="46" fill="#FAF8F4"/><circle cx="292" cy="100" r="30" fill="#F3E3DD"/><circle cx="300" cy="206" r="26" fill="#F3E3DD" opacity=".9"/><circle cx="300" cy="206" r="14" fill="#8E3B3B"/>'+
      '<rect x="228" y="226" width="12" height="54" rx="4" fill="#FAF8F4" opacity=".85"/></svg>';
    }else if(e.cat==='Musik'){
      s='<svg '+W+'><defs><linearGradient id="'+id+'" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#3A2F2C"/><stop offset="1" stop-color="#6B4234"/></linearGradient></defs><rect width="400" height="300" fill="url(#'+id+')"/>'+
      '<path d="M200 0 L90 230 H310Z" fill="#F3D9C6" opacity=".16"/><path d="M80 0 L10 200 H150Z" fill="#E9B79B" opacity=".12"/>'+
      '<rect y="230" width="400" height="70" fill="#2A211F"/><ellipse cx="200" cy="232" rx="110" ry="10" fill="#E9B79B" opacity=".35"/>'+
      '<circle cx="165" cy="150" r="17" fill="#18110F"/><path d="M142 232 Q165 172 188 232Z" fill="#18110F"/>'+
      '<circle cx="245" cy="156" r="16" fill="#18110F"/><path d="M224 232 Q245 180 266 232Z" fill="#18110F"/>'+
      '<path d="M268 190 L330 120" stroke="#C07A64" stroke-width="5"/><ellipse cx="332" cy="200" rx="30" ry="40" fill="#C07A64" opacity=".9"/>'+
      '<circle cx="300" cy="50" r="3" fill="#FAF8F4" opacity=".7"/><circle cx="60" cy="80" r="2" fill="#FAF8F4" opacity=".6"/></svg>';
    }else if(e.cat==='Markt'){
      s='<svg '+W+'><rect width="400" height="300" fill="'+(v?'#E8D9C8':'#EAD7C5')+'"/><rect y="220" width="400" height="80" fill="#CDB9A4"/>';
      [[20,'#B96E58'],[210,'#8E9A7B']].forEach(function(st){
        var x=st[0];s+='<rect x="'+(x+8)+'" y="110" width="8" height="120" fill="#8A6A55"/><rect x="'+(x+152)+'" y="110" width="8" height="120" fill="#8A6A55"/><rect x="'+x+'" y="190" width="170" height="40" fill="#F3E3DD"/>';
        for(var z=0;z<8;z++){s+='<path d="M'+(x+z*21.25)+' 80 H'+(x+(z+1)*21.25)+' V112 Q'+(x+z*21.25+10.6)+' 124 '+(x+z*21.25)+' 112Z" fill="'+(z%2?'#FAF8F4':st[1])+'"/>'}
        s+='<circle cx="'+(x+40)+'" cy="178" r="12" fill="#C07A64"/><circle cx="'+(x+78)+'" cy="178" r="12" fill="#D9A27F"/><circle cx="'+(x+116)+'" cy="178" r="12" fill="#9A9C7E"/>';
      });
      s+='<path d="M0 40 Q100 78 200 40 T400 40" fill="none" stroke="#8A6A55" stroke-width="2"/>';
      for(var q=0;q<10;q++){s+='<circle cx="'+(20+q*40)+'" cy="'+(q%2?52:46)+'" r="5" fill="#F4C27A"/>'}
      s+='</svg>';
    }else if(e.cat==='Kultur'){
      s='<svg '+W+'><rect width="400" height="300" fill="'+(v?'#D8C3B5':'#D4BFB0')+'"/><rect y="230" width="400" height="70" fill="#B89B88"/>';
      [60,170,280].forEach(function(x,j){s+='<path d="M'+x+' 240 V120 Q'+(x+30)+' 70 '+(x+60)+' 120 V240Z" fill="#FAEBD7"/><path d="M'+(x+8)+' 240 V124 Q'+(x+30)+' 86 '+(x+52)+' 124 V240Z" fill="#F4C27A" opacity="'+(j===1?.95:.55)+'"/><rect x="'+(x+28)+'" y="110" width="4" height="130" fill="#B89B88" opacity=".7"/>'});
      s+='<rect x="30" y="100" width="340" height="12" fill="#A68672" opacity=".5"/></svg>';
    }else{
      s='<svg '+W+'><rect width="400" height="300" fill="'+(v?'#F3E3DD':'#F0DACF')+'"/><circle cx="330" cy="70" r="150" fill="#E9B79B" opacity=".35"/><rect x="40" y="190" width="320" height="26" rx="13" fill="#C9A58E" opacity=".55"/>';
      [[90,150,'#C07A64'],[170,130,'#D9A27F'],[250,155,'#9A9C7E'],[310,125,'#C07A64']].forEach(function(c){s+='<circle cx="'+c[0]+'" cy="'+c[1]+'" r="38" fill="'+c[2]+'"/><path d="M'+(c[0]-14)+' '+c[1]+' h28 M'+c[0]+' '+(c[1]-14)+' v28" stroke="#FAF8F4" stroke-width="5" stroke-linecap="round" opacity=".85"/>'});
      s+='<path d="M0 36 Q100 70 200 36 T400 36" fill="none" stroke="#A9644F" stroke-width="2"/>';
      for(var f=0;f<8;f++){s+='<path d="M'+(25+f*50)+' '+(f%2?58:46)+' l8 22 l-16 0Z" fill="'+(f%2?'#C07A64':'#FAF8F4')+'"/>'}
      s+='</svg>';
    }
    return s;
  }
  function art(e){
    var v=e.i%2,id='g'+e.i+(Math.random()*1e4|0),s='';
    var G='#2F5D46',GD='#1B3A2C',GM='#5F8A66',SG='#A9BC95',BU='#D9A92E',BL='#F2D675',CR='#F6F3E8',WA='#E8E0C8';
    var W='viewBox="0 0 400 300" preserveAspectRatio="xMidYMid slice" role="img" aria-label="Platzhalter-Bild '+esc(e.cat)+'"';
    if(e.cat==='Wein'){
      s='<svg '+W+'><defs><linearGradient id="'+id+'" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="'+(v?'#F6E7B8':CR)+'"/><stop offset="1" stop-color="'+(v?'#EBC76A':WA)+'"/></linearGradient></defs><rect width="400" height="300" fill="url(#'+id+')"/><circle cx="'+(v?300:100)+'" cy="92" r="34" fill="'+BU+'"/><path d="M0 150 Q120 110 230 140 T400 120 V300 H0Z" fill="'+SG+'"/><path d="M0 190 Q140 150 260 180 T400 165 V300 H0Z" fill="'+GM+'"/>';
      for(var k=0;k<9;k++){s+='<path d="M'+(-20+k*10)+' 300 Q'+(120+k*22)+' '+(200-k*4)+' 420 '+(190+k*12)+'" fill="none" stroke="'+GD+'" stroke-width="3" opacity=".4"/>'}
      s+='<path d="M0 250 Q200 220 400 250 V300 H0Z" fill="'+G+'"/><rect x="40" y="188" width="26" height="22" fill="'+CR+'"/><path d="M36 188 L53 172 L70 188Z" fill="'+GD+'"/></svg>';
    }else if(e.cat==='Kulinarik'){
      s='<svg '+W+'><rect width="400" height="300" fill="'+(v?G:GM)+'"/><g opacity=".14" stroke="#fff"><path d="M0 60H400M0 130H400M0 200H400M0 270H400"/></g><circle cx="130" cy="150" r="82" fill="'+CR+'"/><circle cx="130" cy="150" r="58" fill="none" stroke="'+WA+'" stroke-width="3"/><circle cx="130" cy="150" r="30" fill="'+BL+'"/><circle cx="118" cy="142" r="10" fill="'+G+'"/><circle cx="146" cy="160" r="9" fill="'+BU+'"/><circle cx="292" cy="100" r="46" fill="'+CR+'"/><circle cx="292" cy="100" r="30" fill="'+WA+'"/><circle cx="300" cy="206" r="26" fill="'+WA+'"/><circle cx="300" cy="206" r="14" fill="'+BU+'"/><rect x="228" y="226" width="12" height="54" rx="4" fill="'+CR+'" opacity=".85"/></svg>';
    }else if(e.cat==='Musik'){
      s='<svg '+W+'><defs><linearGradient id="'+id+'" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="'+GD+'"/><stop offset="1" stop-color="'+G+'"/></linearGradient></defs><rect width="400" height="300" fill="url(#'+id+')"/><path d="M200 0 L90 230 H310Z" fill="'+BL+'" opacity=".16"/><path d="M80 0 L10 200 H150Z" fill="'+SG+'" opacity=".14"/><rect y="230" width="400" height="70" fill="#0F2219"/><ellipse cx="200" cy="232" rx="110" ry="10" fill="'+BU+'" opacity=".4"/><circle cx="165" cy="150" r="17" fill="#0A1710"/><path d="M142 232 Q165 172 188 232Z" fill="#0A1710"/><circle cx="245" cy="156" r="16" fill="#0A1710"/><path d="M224 232 Q245 180 266 232Z" fill="#0A1710"/><path d="M268 190 L330 120" stroke="'+BU+'" stroke-width="5"/><ellipse cx="332" cy="200" rx="30" ry="40" fill="'+BU+'" opacity=".9"/><circle cx="300" cy="50" r="3" fill="'+CR+'" opacity=".7"/><circle cx="60" cy="80" r="2" fill="'+CR+'" opacity=".6"/></svg>';
    }else if(e.cat==='Markt'){
      s='<svg '+W+'><rect width="400" height="300" fill="'+(v?CR:WA)+'"/><rect y="220" width="400" height="80" fill="#D8CFB1"/>';
      [[20,G],[210,BU]].forEach(function(st){
        var x=st[0];s+='<rect x="'+(x+8)+'" y="110" width="8" height="120" fill="'+GD+'"/><rect x="'+(x+152)+'" y="110" width="8" height="120" fill="'+GD+'"/><rect x="'+x+'" y="190" width="170" height="40" fill="'+WA+'"/>';
        for(var z=0;z<8;z++){s+='<path d="M'+(x+z*21.25)+' 80 H'+(x+(z+1)*21.25)+' V112 Q'+(x+z*21.25+10.6)+' 124 '+(x+z*21.25)+' 112Z" fill="'+(z%2?CR:st[1])+'"/>'}
        s+='<circle cx="'+(x+40)+'" cy="178" r="12" fill="'+BU+'"/><circle cx="'+(x+78)+'" cy="178" r="12" fill="'+SG+'"/><circle cx="'+(x+116)+'" cy="178" r="12" fill="'+GM+'"/>';
      });
      s+='<path d="M0 40 Q100 78 200 40 T400 40" fill="none" stroke="'+GD+'" stroke-width="2"/>';
      for(var q=0;q<10;q++){s+='<circle cx="'+(20+q*40)+'" cy="'+(q%2?52:46)+'" r="5" fill="'+BL+'"/>'}
      s+='</svg>';
    }else if(e.cat==='Kultur'){
      s='<svg '+W+'><rect width="400" height="300" fill="'+(v?SG:'#B7C7A3')+'"/><rect y="230" width="400" height="70" fill="#8FA37F"/>';
      [60,170,280].forEach(function(x,j){s+='<path d="M'+x+' 240 V120 Q'+(x+30)+' 70 '+(x+60)+' 120 V240Z" fill="'+CR+'"/><path d="M'+(x+8)+' 240 V124 Q'+(x+30)+' 86 '+(x+52)+' 124 V240Z" fill="'+BL+'" opacity="'+(j===1?.95:.55)+'"/><rect x="'+(x+28)+'" y="110" width="4" height="130" fill="#8FA37F" opacity=".7"/>'});
      s+='<rect x="30" y="100" width="340" height="12" fill="'+GM+'" opacity=".5"/></svg>';
    }else{
      s='<svg '+W+'><rect width="400" height="300" fill="'+(v?CR:'#F1EAD0')+'"/><circle cx="330" cy="70" r="150" fill="'+SG+'" opacity=".4"/><rect x="40" y="190" width="320" height="26" rx="13" fill="'+WA+'"/>';
      [[90,150,G],[170,130,BU],[250,155,GM],[310,125,G]].forEach(function(c){s+='<circle cx="'+c[0]+'" cy="'+c[1]+'" r="38" fill="'+c[2]+'"/><path d="M'+(c[0]-14)+' '+c[1]+' h28 M'+c[0]+' '+(c[1]-14)+' v28" stroke="'+CR+'" stroke-width="5" stroke-linecap="round" opacity=".9"/>'});
      s+='<path d="M0 36 Q100 70 200 36 T400 36" fill="none" stroke="'+GD+'" stroke-width="2"/>';
      for(var f=0;f<8;f++){s+='<path d="M'+(25+f*50)+' '+(f%2?58:46)+' l8 22 l-16 0Z" fill="'+(f%2?BU:G)+'"/>'}
      s+='</svg>';
    }
    return s;
  }
  function pic(e,badge){return '<div class="pic">'+thumb(e)+'<span class="chipdate">'+short(e.date)+' · '+e.t+'</span>'+(badge?'<span class="chipbadge">'+badge+'</span>':'')+'</div>'}
  function badgeOf(e){return e.tier==='premium'?'Premium':e.tier==='top'?'Top':''}
  function card(e){return '<button class="ev'+((e.tier==='top'||e.tier==='premium')?' hl':'')+'" data-i="'+e.i+'">'+pic(e,badgeOf(e))+'<div class="txt"><h3>'+esc(e.title)+'</h3><div class="meta"><span>'+esc(e.place)+', '+esc(e.town)+'</span><span class="sub"><span class="tm">'+e.t+' · </span>'+esc(e.cat)+' · '+esc(e.price)+(e.multi?' · bis '+dmy(e.last):'')+(e.tier!=='free'?' <span class="ad">Anzeige</span>':'')+'</span></div></div></button>'}

  function range(e){return dmy(e.dates[0][0])+' – '+dmy(e.last)}
  function lcard(e){var run=e.dates[0][0]<=iso(base);return '<button class="ev'+((e.tier==='top'||e.tier==='premium')?' hl':'')+'" data-i="'+e.i+'"><div class="pic">'+thumb(e)+'<span class="chipdate">'+range(e)+'</span>'+(run?'<span class="chipbadge">Läuft gerade</span>':'')+'</div><div class="txt"><h3>'+esc(e.title)+'</h3><div class="meta"><span>'+esc(e.place)+', '+esc(e.town)+'</span><span class="sub">'+esc(e.cat)+' · '+esc(e.price)+(e.tier!=='free'?' <span class="ad">Anzeige</span>':'')+'</span></div></div></button>'}
  function uniq(a){var seen={};return a.filter(function(e){if(seen[e.title])return false;seen[e.title]=1;return true})}

  /* Filterlogik */
  function matchTime(e){
    var d=days(e),w=e.date.getDay();
    switch(F.time){
      case 'today':return d===0;
      case 'tomorrow':return d===1;
      case 'weekend':var dow=base.getDay();var toSat=dow===0?-1:(6-dow);return d>=toSat&&d<=toSat+1&&(w===6||w===0);
      case '7':return d<=7;
      case '30':return d<=30;
      case 'long':return e.long;
      case 'date':return F.date?iso(e.date)===F.date:true;
      default:return true;
    }
  }
  function match(e,skip){
    if(skip!=='q'&&F.q){var parts=F.q.toLowerCase().split(/\s+/).filter(Boolean);for(var i=0;i<parts.length;i++){if(e.hay.indexOf(parts[i])<0)return false}}
    if(!matchTime(e))return false;
    if(F.cats.length&&F.cats.indexOf(e.cat)<0)return false;
    if(F.towns.length&&F.towns.indexOf(e.town)<0)return false;
    if(F.free&&!/^(gratis|spende)/i.test(e.price))return false;
    return true;
  }
  function visible(){return EV.filter(function(e){return match(e)}).sort(function(a,b){return a.date-b.date||a.t.localeCompare(b.t)})}
  function activeCount(){return (F.time!=='all'?1:0)+F.cats.length+F.towns.length+(F.free?1:0)}
  function timeName(){return {today:'Heute',tomorrow:'Morgen',weekend:'Wochenende','7':'Nächste 7 Tage','30':'Nächste 30 Tage','long':'Mehrtägig'}[F.time]||(F.time==='date'&&F.date?F.date.split('-').reverse().join('.'):'')}
  function toggle(arr,v){var i=arr.indexOf(v);if(i<0)arr.push(v);else arr.splice(i,1)}
  function resetAll(){F.time='all';F.date='';F.cats=[];F.towns=[];F.free=false;F.q='';$('#q').value='';render();if($('#filterScrim').classList.contains('open'))renderFilter()}

  function mk(txt,pressed,cls,fn){var b=document.createElement('button');b.className='chip '+(cls||'');b.innerHTML=txt;b.setAttribute('aria-pressed',pressed);b.onclick=fn;return b}
  function renderQuick(){
    var box=$('#quick');box.innerHTML='';
    [['all','Alle'],['today','Heute'],['tomorrow','Morgen'],['weekend','Wochenende'],['30','30 Tage'],['long','Mehrtägig']].forEach(function(t){box.appendChild(mk(t[1],F.time===t[0],'',function(){F.time=t[0];F.date='';render()}))});
  }

  function render(){
    renderQuick();
    var qc2=$('#qclr');if(qc2)qc2.hidden=!$('#q').value;
    var bn=$('#banner');
    if(S.banner&&C.btxt){bn.innerHTML='<button class="bnr" id="bnrBtn" type="button">'+esc(C.btxt)+' <span>›</span></button>';$('#bnrBtn').onclick=function(){F.q=C.blink||'';$('#q').value=F.q;render();var l=$('#list');if(l)l.scrollIntoView({behavior:'smooth'})}}else bn.innerHTML='';
    var n=activeCount();$('#fcount').textContent=n?n:'';
    var all=visible(),isLong=F.time==='long',showRow=F.time==='all'&&!F.q,lg=uniq(all.filter(function(e){return e.long})),listAll=isLong?uniq(all):all;
    var parts=[];if(F.q)parts.push('„'+F.q+'“');if(F.time!=='all'&&timeName())parts.push(timeName());F.cats.forEach(function(c){parts.push(c)});F.towns.forEach(function(c){parts.push(c)});if(F.free)parts.push('Gratis');
    var pl=[];
    if(F.q)pl.push(['q','','„'+F.q+'“']);
    if(['7','date'].indexOf(F.time)>-1&&timeName())pl.push(['time','',timeName()]);
    F.cats.forEach(function(c){pl.push(['cat',c,c])});F.towns.forEach(function(c){pl.push(['town',c,c])});if(F.free)pl.push(['free','','Gratis']);
    var pe=$('#pills');pe.hidden=!pl.length;
    pe.innerHTML=pl.map(function(p){return '<button class="pill" data-k="'+p[0]+'" data-v="'+esc(p[1])+'">'+esc(p[2])+'<b aria-hidden="true">×</b></button>'}).join('');
    pe.querySelectorAll('.pill').forEach(function(b){b.onclick=function(){var k=b.dataset.k,v=b.dataset.v;if(k==='q'){F.q='';$('#q').value=''}else if(k==='time'){F.time='all';F.date=''}else if(k==='cat')toggle(F.cats,v);else if(k==='town')toggle(F.towns,v);else F.free=false;render()}});
    var fe=[];
    if(!parts.length){(C.top||[]).forEach(function(t){t=String(t).toLowerCase();var m=all.filter(function(e){return e.title.toLowerCase().indexOf(t)>-1})[0];if(m&&fe.indexOf(m)<0&&!fe.some(function(x){return x.title===m.title}))fe.push(m)});
      if(fe.length<4)all.filter(function(e){return e.tier==='top'||e.tier==='premium'}).forEach(function(e){if(fe.length<4&&!fe.some(function(x){return x.title===e.title}))fe.push(e)});fe=fe.slice(0,4)}
    function wire(box){var sw=box&&box.querySelector('.snapwrap');if(!sw)return;var sn=sw.querySelector('.snap'),pv=sw.querySelector('.prev'),nx=sw.querySelector('.next');var upd=function(){pv.hidden=sn.scrollLeft<8;nx.hidden=sn.scrollLeft+sn.clientWidth>=sn.scrollWidth-8};pv.onclick=function(){sn.scrollBy({left:-sn.clientWidth*.8,behavior:'smooth'})};nx.onclick=function(){sn.scrollBy({left:sn.clientWidth*.8,behavior:'smooth'})};sn.addEventListener('scroll',upd,{passive:true});setTimeout(upd,50)}
    function row(items,fn,head,mt){return '<div class="section-head" style="margin-top:'+mt+'px">'+head+'</div><div class="snapwrap"><button type="button" class="snapnav prev" aria-label="Zurück" hidden>‹</button><div class="snap sm">'+items.map(fn).join('')+'</div><button type="button" class="snapnav next" aria-label="Weiter">›</button></div>'}
    var fb=$('#featuredBox');fb.innerHTML=fe.length?row(fe,card,'<div><span class="label">Unsere Auswahl</span><h2>Top Events</h2></div><span class="label">'+fe.length+' Events</span>',0):'';wire(fb);
    var lb=$('#longBox');if(lb){lb.innerHTML=showRow&&lg.length?row(lg,lcard,'<h2>Mehrtägig und laufend</h2><span class="label">'+lg.length+(lg.length===1?' Event':' Events')+'</span>',fe.length?46:0):'';wire(lb)}
    var wk=$('#weekBox');
    if(!parts.length){
      var picks=[['Jazz im Weinkeller','Kleiner Keller, großer Sound. Ideal für einen Abend zu zweit.'],['Weinwanderung im Dezember','Zwei Stunden durch die Terrassen, vier Stationen mit Verkostung.'],['Plätzchen backen für alle','Offene Backstube, perfekt für Familien am Nachmittag.']];
      picks=C.wk||[];var pk=picks.map(function(p){return EV.filter(function(x){return x.title===p[0]})[0]?p:null}).filter(Boolean);if(pk.length)wk.innerHTML='<section class="week"><span class="label">Unsere Wochenempfehlung</span><h2>Drei Tipps, die wir empfehlen</h2><p>Von uns ausgewählt, damit du nicht suchen musst.</p>'+picks.map(function(p){var e=EV.filter(function(x){return x.title===p[0]})[0];return e?'<button class="wk" data-i="'+e.i+'"><span class="wkpic">'+art(e)+'</span><span><b>'+esc(e.title)+'</b><small>'+esc(p[1])+'</small></span></button>':''}).join('')+'</section>';else wk.innerHTML='';
      wk.querySelectorAll('.wk').forEach(function(b){b.onclick=function(){openDetail(EV[+b.dataset.i])}});
    }else wk.innerHTML='';
    var html='';
    if(!listAll.length){
      var near=EV.filter(function(e){return match(e,'q')}).length;
      html='<div class="empty"><h3>Hier ist gerade nichts los</h3><p>'+(F.q?'Zu „'+esc(F.q)+'“ gibt es noch nichts. Versuch einen anderen Begriff.':'Mit weniger Filtern findest du bestimmt etwas.')+'</p><div class="row2"><button class="btn ghost" id="empty-reset">Zurücksetzen</button><button class="btn accent" data-open-submit>Event eintragen</button></div></div>';
    }else{
      var last='';
      if(isLong){html='<div class="list cols">'+listAll.map(lcard).join('')+'</div>'}else var cut=new Date();cut.setHours(0,0,0,0);cut.setDate(cut.getDate()+7);var fold=!isLong&&F.time==='all'&&!F.q&&!MORE,rest=0;listAll.forEach(function(e){if(fold&&e.date>=cut){rest++;return}var k=e.date.toDateString();if(k!==last){if(last)html+='</div></section>';html+='<section class="dgrp"><div class="day"><strong>'+lbl(e.date)+'</strong></div><div class="list cols">';last=k}html+=card(e)});
      if(!isLong&&last)html+='</div></section>';
      if(rest)html+='<button type="button" class="btn ghost" id="moreDays" style="display:block;margin:24px auto 0">Weitere '+rest+' Einträge anzeigen</button>';
    }
    $('#list').innerHTML='<div class="section-head" style="margin-top:'+((fe.length||(showRow&&lg.length))?46:0)+'px"><h2>'+(parts.length?'Ergebnisse':'Alle Veranstaltungen')+'</h2><span class="label">'+listAll.length+' Einträge</span></div>'+html;
    document.querySelectorAll('.ev').forEach(function(b){b.onclick=function(){openDetail(EV[+b.dataset.i])}});
    var er=$('#empty-reset');if(er)er.onclick=resetAll;
    var md=$('#moreDays');if(md)md.onclick=function(){MORE=true;render()};
  }

  function lock(on){document.body.style.overflow=on?'hidden':''}
  function openScrim(id){$(id).classList.add('open');lock(1)}
  function closeScrim(id){$(id).classList.remove('open');if(!document.querySelector('.scrim.open'))lock(0)}
  ['#detailScrim','#submitScrim','#filterScrim','#regionScrim'].forEach(function(id){$(id).addEventListener('click',function(ev){if(ev.target===$(id))closeScrim(id)})});
  document.addEventListener('keydown',function(ev){if(ev.key==='Escape'){['#detailScrim','#submitScrim','#filterScrim','#regionScrim'].forEach(closeScrim)}});

  /* Filter-Sheet */
  function renderFilter(){
    var s=$('#filterSheet'),top=s.scrollTop,cnt=(F.time==='long'?uniq(visible()):visible()).length;
    var times=[['all','Alle'],['today','Heute'],['tomorrow','Morgen'],['weekend','Wochenende'],['7','7 Tage'],['30','30 Tage'],['long','Mehrtägig'],['date','Datum wählen']];
    function townCount(t){return EV.filter(function(e,i,a){return e.town===t&&a.findIndex(function(x){return x.title===e.title})===i}).length}
    s.innerHTML='<div class="pad"><div class="sheet-top"><h2 style="margin:0">Filter</h2><button class="x" id="fx" aria-label="Schließen">×</button></div>'+
      '<div class="fgroup"><span class="label">Wann</span><div class="wrapchips" id="ft"></div><div id="fdate"></div></div>'+
      '<div class="fgroup"><span class="label">Was</span><div class="wrapchips" id="fc"></div></div>'+
      '<div class="fgroup"><span class="label">Wo</span><div class="wrapchips" id="fw"></div></div>'+
      '<div class="fgroup"><span class="label">Preis</span><div class="wrapchips" id="fp"></div></div></div>'+
      '<div class="stickybtns"><button class="btn ghost" id="fr">Zurücksetzen</button><button class="btn accent" id="fs">'+cnt+(cnt===1?' Event':' Events')+' anzeigen</button></div>';
    var ft=$('#ft');times.forEach(function(t){ft.appendChild(mk(t[1],F.time===t[0],'',function(){F.time=t[0];if(t[0]!=='date')F.date='';render();renderFilter()}))});
    if(F.time==='date'){$('#fdate').innerHTML='<input type="date" id="fd" min="'+iso(base)+'" value="'+esc(F.date)+'" aria-label="Datum">';$('#fd').onchange=function(){F.date=this.value;render();renderFilter()}}
    var fc=$('#fc');CATS.forEach(function(c){fc.appendChild(mk(c,F.cats.indexOf(c)>-1,'cat',function(){toggle(F.cats,c);render();renderFilter()}))});
    var fw=$('#fw');fw.className='';[['Krems und Umgebung',['Krems','Stein','Furth bei Göttweig']],['Wachau',['Dürnstein','Spitz','Weißenkirchen']],['Kamptal',['Langenlois','Gobelsburg']],['Traisental',['Traismauer','Herzogenburg']]].forEach(function(g){var l=document.createElement('span');l.className='sublabel';l.textContent=g[0];fw.appendChild(l);var w=document.createElement('div');w.className='wrapchips';g[1].forEach(function(t){w.appendChild(mk(esc(t)+'<small>'+townCount(t)+'</small>',F.towns.indexOf(t)>-1,'cat',function(){toggle(F.towns,t);render();renderFilter()}))});fw.appendChild(w)});
    $('#fp').appendChild(mk('Nur gratis',F.free,'',function(){F.free=!F.free;render();renderFilter()}));
    $('#fx').onclick=function(){closeScrim('#filterScrim')};
    $('#fs').onclick=function(){closeScrim('#filterScrim');window.scrollTo({top:0})};
    $('#fr').onclick=resetAll;
    s.scrollTop=top;
  }
  $('#fopen').onclick=function(){renderFilter();openScrim('#filterScrim')};
  var qt;$('#q').addEventListener('input',function(){var v=this.value;clearTimeout(qt);qt=setTimeout(function(){F.q=v.trim();render()},120)});

  /* Region */
  $('#regionBtn').onclick=function(){
    $('#regionSheet').innerHTML='<div class="pad"><div class="sheet-top"><h2 style="margin:0">Region</h2><button class="x" id="rx" aria-label="Schließen">×</button></div>'+
      '<div class="rlist"><button class="ritem on"><span><b>Region Krems</b><small>Krems | Wachau, Kremstal, Kamptal, Traisental</small></span><span class="badge">Aktiv</span></button>'+
      '</div>'+
      '<button class="btn ghost block" id="rc">Schließen</button></div>';
    $('#rx').onclick=$('#rc').onclick=function(){closeScrim('#regionScrim')};
    openScrim('#regionScrim');
  };

  function openDetail(e){
    var s=$('#detailSheet'),more=EV.filter(function(x){return x.org===e.org&&x.title!==e.title}).slice(0,2);
    s.innerHTML='<div style="position:relative">'+pic(e,badgeOf(e)).replace('class="chipbadge"','class="chipbadge" style="top:auto;bottom:14px"')+'<button class="x" aria-label="Schließen" id="dx">×</button></div>'+
      '<div class="pad"><div class="evtop"><span class="label">'+esc(e.cat)+((e.tier==='top'||e.tier==='premium')?' · Anzeige':'')+'</span><button class="shareic" type="button" id="d_shareic" aria-label="Teilen"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 15V3"/><path d="M7.5 7.5 12 3l4.5 4.5"/><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-7"/></svg></button></div><h2>'+esc(e.title)+'</h2><p>'+esc(e.desc)+'</p>'+
      '<div class="facts"><div><span>'+(e.multi?'Zeitraum':'Datum')+'</span><span>'+(e.multi?dmy(e.dates[0][0])+' – '+dmy(e.last):lbl(e.date))+'</span></div><div><span>'+(e.multi?'Nächster Termin':'Beginn')+'</span><span>'+(e.multi?lbl(e.date)+', ':'')+e.t+(e.tend?'–'+e.tend:'')+' Uhr</span></div><div><span>Ort</span><span>'+esc(e.place)+', '+esc(e.town)+'</span></div><div><span>Preis</span><span>'+esc(e.price)+'</span></div><div><span>Veranstalter</span><span>'+esc(e.org)+'</span></div>'+(e.image&&(e.credit||e.org)?'<div><span>Bildnachweis</span><span>© '+esc(e.credit||e.org)+'</span></div>':'')+'</div>'+(e.multi?'<details style="margin:-8px 0 20px"><summary style="cursor:pointer;font-weight:600">Alle '+e.dates.length+' Termine</summary><div style="margin-top:10px;font-size:14px;color:var(--muted);line-height:1.8">'+e.dates.map(function(x){return short(dd(x[0]))+' · '+x[1]+' Uhr'}).join('<br>')+'</div></details>':'')+
      
      (e.link?'<a class="btn accent block" target="_blank" rel="noopener nofollow" href="'+esc(e.link)+'">Tickets / Anmeldung</a>':'')+
      '<p class="note" style="margin:18px 0 0;font-size:12.5px">Angaben und Bilder stammen von den Veranstaltern. Für Inhalt, Richtigkeit und Bildrechte sind allein diese verantwortlich, nicht ewent. bzw. der Betreiber der Website.</p><div class="dact"><button class="btn ghost" id="d_share" type="button">Teilen</button><button class="btn ghost" id="d_cal" type="button">Zum Kalender</button><a class="btn ghost" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query='+encodeURIComponent(e.place+', '+e.town)+'">Anfahrt</a></div>'+
      (e.manual?'<div class="fnote" style="margin-top:26px;text-align:left"><b>Manuell angelegt.</b> Dieses Event haben wir manuell angelegt (alle Events im November und Dezember). Bei Änderungen schick uns bitte kurz eine Mail an <a href="mailto:info@ewent.at?subject='+encodeURIComponent('Änderung: '+e.title)+'">info@ewent.at</a>.</div>':'')+(more.length?'<div class="agrp" style="margin-top:26px"><span class="label">Mehr von '+esc(e.org)+'</span>'+more.map(function(x){return '<button class="wk" data-mi="'+x.i+'"><span class="wkpic">'+thumb(x)+'</span><span><b>'+esc(x.title)+'</b><small>'+short(x.date)+' · '+x.t+'</small></span></button>'}).join('')+'</div>':'')+'</div>';
    $('#dx').onclick=function(){closeScrim('#detailScrim')};
    var shareIc=$('#d_shareic');$('#d_share').onclick=shareIc.onclick=function(){var b=this,txt=e.title+' · '+lbl(e.date);if(navigator.share){navigator.share({title:e.title,text:txt,url:e.url||location.href}).catch(function(){})}else{try{navigator.clipboard.writeText(txt+' '+(e.url||location.href))}catch(x){}if(b.id==='d_share'){b.textContent='Kopiert';setTimeout(function(){b.textContent='Teilen'},1600)}}};
    $('#d_cal').onclick=function(){var b=this;try{var blob=new Blob([ics(e)],{type:'text/calendar'}),a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download='event.ics';document.body.appendChild(a);a.click();a.remove()}catch(x){}b.textContent='Geladen';setTimeout(function(){b.textContent='Zum Kalender'},1600)};
    s.querySelectorAll('[data-mi]').forEach(function(b){b.onclick=function(){openDetail(EV[+b.dataset.mi]);$('#detailSheet').scrollTop=0}});
    openScrim('#detailScrim');
  }

  function ics(e){var p=function(n){return (n<10?'0':'')+n},h=e.t.split(':'),ymd=e.date.getFullYear()+p(e.date.getMonth()+1)+p(e.date.getDate()),st=ymd+'T'+h[0]+h[1]+'00',en=ymd+'T'+p((+h[0]+2)%24)+h[1]+'00';return ['BEGIN:VCALENDAR','VERSION:2.0','PRODID:-//ewent.//DE','BEGIN:VEVENT','UID:'+e.i+'@ewent.','DTSTAMP:'+st,'DTSTART:'+st,'DTEND:'+en,'SUMMARY:'+e.title,'LOCATION:'+e.place+', '+e.town,'DESCRIPTION:'+e.desc,'END:VEVENT','END:VCALENDAR'].join('\r\n')}
  function packHTML(p){
    return '<div class="pack'+(p.hot?' hot':'')+(p.live?'':' soon')+'"><div class="row"><h3>'+p.name+'</h3>'+(p.hot?'<span class="badge">Beliebt</span>':'')+'</div>'+
      '<div class="price">'+'€ '+p.price+'</div><small class="psub">'+'pro Event'+'</small>'+(p.desc?'<p class="pdesc">'+esc(p.desc)+'</p>':'')+'<div class="from"><span>'+p.from+'</span></div><ul>'+p.items.map(function(i){return '<li>'+esc(i)+'</li>'}).join('')+'</ul>'+
      (!p.live?'<button class="btn ghost block" disabled>Demnächst verfügbar</button>':'<button class="btn ghost block" data-pack="'+p.id+'">Gratis eintragen</button>')+'</div>';
  }
  $('#packGrid').innerHTML=PACKS.map(packHTML).join('');

  /* Eintragen */
  var form,step;
  function openSubmit(pack){form={title:'',date:'',time:'',place:'',town:'',cat:'',tend:'',desc:'',price:'',link:'',org:'',credit:'',mail:'',tel:'',pack:'free',ai:0,img:'',fly:'',until:''};step=1;renderForm();openScrim('#submitScrim')}
  function steps(){return '<div class="steps">'+[1,2,3,4].map(function(i){return '<i class="'+(i<=step?'on':'')+'"></i>'}).join('')+'</div>'}
  function head(t,sub){return '<div class="sheet-top"><span class="label">Schritt '+step+' von 4</span><button class="x" id="sx" aria-label="Schließen">×</button></div>'+steps()+'<h2>'+t+'</h2><p class="note">'+sub+'</p>'}
  function upl(id,key,title,sub,req){var v=form[key];return '<div class="field"><label for="'+id+'">'+title+(req?' *':'')+'</label>'+(v?'<div class="uplp"><img src="'+v+'" alt="Vorschau"><button type="button" class="btn ghost" data-rm="'+key+'">Entfernen</button></div>':'<label class="upl" for="'+id+'"><span class="ui">↑</span><b>Bild auswählen oder hierher ziehen</b><small>'+sub+'</small></label>')+'<input type="file" id="'+id+'" accept="image/jpeg,image/png,image/webp" hidden></div>'}
  function pVal(p){return !p||p==='Gratis'?'':String(p).replace(/[^\d,]/g,'')}
  function pFmt(r){r=(r||'').replace(/[^\d,]/g,'');return !r||/^0+(,0*)?$/.test(r)?'Gratis':'€ '+r}
  document.addEventListener('input',function(ev){var t=ev.target;if(t.classList&&t.classList.contains('pin')){var v=t.value.replace(/[.]/g,',').replace(/[^\d,]/g,'');var i=v.indexOf(',');if(i>=0)v=v.slice(0,i+1)+v.slice(i+1).replace(/,/g,'').slice(0,2);t.value=v}});
  function val(id){var el=$('#'+id);return el?el.value.trim():''}
  function fmtD(v){var d=new Date(v+'T12:00:00');return DOW[d.getDay()]+', '+d.getDate()+'. '+MON[d.getMonth()]+' '+d.getFullYear()}
  function timeOpts(n,st,sel){var o='<option value="">--</option>',i,v;for(i=0;i<n;i+=st){v=pad2(i);o+='<option value="'+v+'"'+(v===sel?' selected':'')+'>'+v+'</option>'}return o}
  var MONF=['Jänner','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'];
  function dpOpen(id){
    var box=$('#dpcal');if(box){box.remove();if(box.dataset.for===id)return}
    var inp=$('#'+id),btn=$('#'+id+'_b'),min=id==='f_until'?($('#f_date').value||iso(base)):iso(base),cur=inp.value||min,m=dd(cur>min?cur:min);m.setDate(1);
    box=document.createElement('div');box.id='dpcal';box.className='cal';box.dataset.for=id;btn.parentNode.appendChild(box);
    function draw(){
      var y=m.getFullYear(),mo=m.getMonth(),first=(new Date(y,mo,1).getDay()+6)%7,days=new Date(y,mo+1,0).getDate(),h='<div class="calh"><button type="button" class="calnav" data-n="-1" aria-label="Voriger Monat">‹</button><b>'+MONF[mo]+' '+y+'</b><button type="button" class="calnav" data-n="1" aria-label="Nächster Monat">›</button></div><div class="calg">'+['Mo','Di','Mi','Do','Fr','Sa','So'].map(function(x){return '<i>'+x+'</i>'}).join(''),i,v;
      for(i=0;i<first;i++)h+='<span></span>';
      for(i=1;i<=days;i++){v=y+'-'+pad2(mo+1)+'-'+pad2(i);h+='<button type="button" class="cald'+(v===inp.value?' on':'')+(v===iso(base)?' today':'')+'" data-v="'+v+'"'+(v<min?' disabled':'')+'>'+i+'</button>'}
      h+='</div>'+(id==='f_until'&&inp.value?'<button type="button" class="calclr" data-v="">Letzten Tag entfernen</button>':'');
      box.innerHTML=h;
      box.querySelectorAll('.calnav').forEach(function(b){b.onclick=function(){m.setMonth(m.getMonth()+(+b.dataset.n));draw()}});
      box.querySelectorAll('[data-v]').forEach(function(b){b.onclick=function(){var v=b.dataset.v;inp.value=v;btn.textContent=v?fmtD(v):(id==='f_until'?'Optional: letzten Tag wählen':'Datum wählen');if(id==='f_date'){var u=$('#f_until');if(u&&u.value&&u.value<=v){u.value='';var ub=$('#f_until_b');if(ub)ub.textContent='Optional: letzten Tag wählen'}}box.remove()}});
    }
    draw();
  }
  function tpOpen(id){
    var old=$('#tpcal');if(old){old.remove();if(old.dataset.for===id)return}
    var inp=$('#'+id),btn=$('#'+id+'_b'),box=document.createElement('div'),hh='',cur=inp.value;box.id='tpcal';box.className='cal';box.dataset.for=id;btn.parentNode.appendChild(box);
    var lab=id==='f_tend'?'Optional: Ende wählen':'Uhrzeit wählen';
    function done(v){inp.value=v;btn.textContent=v?v+' Uhr':lab;box.remove()}
    function draw(){
      var h='<div class="tph">'+(hh?'Minute für '+hh+' Uhr':'Stunde')+'</div><div class="tpg">',i;
      if(!hh){for(i=6;i<30;i++){var x=pad2(i%24);h+='<button type="button" class="tpb'+(cur&&cur.slice(0,2)===x?' on':'')+'" data-h="'+x+'">'+x+'</button>'}}
      else{for(i=0;i<60;i+=5){var m=pad2(i);h+='<button type="button" class="tpb'+(cur===hh+':'+m?' on':'')+'" data-m="'+m+'">'+m+'</button>'}}
      h+='</div>'+(hh?'<button type="button" class="calclr" data-back="1">Andere Stunde</button>':'')+(id==='f_tend'&&inp.value?'<button type="button" class="calclr" data-clr="1">Ende entfernen</button>':'');
      box.innerHTML=h;
      box.querySelectorAll('[data-h]').forEach(function(b){b.onclick=function(){hh=b.dataset.h;draw()}});
      box.querySelectorAll('[data-m]').forEach(function(b){b.onclick=function(){done(hh+':'+b.dataset.m)}});
      var bk=box.querySelector('[data-back]');if(bk)bk.onclick=function(){hh='';draw()};
      var cl=box.querySelector('[data-clr]');if(cl)cl.onclick=function(){done('')};
    }
    draw();
  }
  function need(ids){var bad=null;ids.forEach(function(id){var el=$('#'+id),v=el?el.value.trim():'',bx=el&&(el.closest('.field')||el.parentNode);if(bx)bx.classList.toggle('bad',!v);if(!v&&!bad)bad=bx||el});if(bad){bad.scrollIntoView({block:'center',behavior:'smooth'});toast('Bitte alle Pflichtfelder ausfüllen.');setTimeout(function(){toast('')},3500);return false}return true}
  function save(){var m={f_title:'title',f_date:'date',f_time:'time',f_tend:'tend',f_cat:'cat',f_until:'until',f_place:'place',f_town:'town',f_desc:'desc',f_price:'price',f_link:'link',f_org:'org',f_credit:'credit',f_mail:'mail',f_tel:'tel'};Object.keys(m).forEach(function(k){if($('#'+k))form[m[k]]=val(k)});if($('#f_price')){form.priceSet=val('f_price')!=='';form.price=pFmt(val('f_price'))}}
  function renderForm(){
    var s=$('#submitPad'),h='';
    if(step===1){
      h=head('Wie sichtbar?','Zum Start ist alles gratis, auch Ticket-Link und mehrtägige Events.')+
      '<div class="opt">'+PACKS.map(function(p){var sub=p.id==='free'?'Startaktion: inklusive Ticket-Link und mehrtägigen Events':p.id==='plus'?'Ticket-Button und mehrtägige Events':p.id==='top'?'Hervorgehoben und Top-Platzierung auf der Startseite':'Top plus Newsletter und Social, wir melden uns persönlich';return '<button type="button" data-p="'+p.id+'"'+(p.live?'':' disabled class="soon"')+' aria-pressed="'+(form.pack===p.id)+'"><span><b>'+p.name+'</b><small>'+sub+'</small></span><span class="p">'+(!p.live?'Demnächst':'€ 0')+'</span></button>'}).join('')+'</div>'+
      '<p class="note">Du kannst ein Paket auch später ändern.</p>'+
      '<div class="nav"><button class="btn" id="next">Weiter</button></div>';
    }else if(step===2){
      h=head('Worum geht’s?',form.ai?'Von der KI ausgelesen. Bitte kurz prüfen, besonders die markierte Uhrzeit.':'Titel, Termin und Ort. Du hast einen Flyer? Schick ihn an info@ewent.at, wir tragen das Event für dich ein.')+
      '<div class="field"><label for="f_title">Titel *</label><input class="in" id="f_title" value="'+esc(form.title)+'" placeholder="z. B. Hofheuriger im Herbst"></div>'+
      '<div class="field"><label for="f_date_b">Datum *</label><button type="button" class="in dpbtn" id="f_date_b" data-dp="f_date">'+(form.date?fmtD(form.date):'Datum wählen')+'</button><input type="hidden" id="f_date" value="'+esc(form.date)+'"></div>'+
      '<div class="field"><label for="f_time_b">Beginn *'+(form.ai?' (bitte prüfen)':'')+'</label><button type="button" class="in dpbtn'+(form.ai?' flag':'')+'" id="f_time_b" data-tp="f_time">'+(form.time?form.time+' Uhr':'Uhrzeit wählen')+'</button><input type="hidden" id="f_time" value="'+esc(form.time)+'"><div class="tchips">'+['10:00','14:00','18:00','19:30','20:00'].map(function(t){return '<button type="button" class="chip'+(form.time===t?' on':'')+'" data-t="'+t+'">'+t+'</button>'}).join('')+'</div></div>'+
      '<div class="field"><label for="f_tend_b">Ende (optional)</label><button type="button" class="in dpbtn" id="f_tend_b" data-tp="f_tend">'+(form.tend?form.tend+' Uhr':'Optional: Ende wählen')+'</button><input type="hidden" id="f_tend" value="'+esc(form.tend||'')+'"></div>'+
      ((form.pack!=='free'||PROMO)?'<div class="field"><label for="f_until_b">Letzter Tag (nur bei mehrtägigen Events)</label><button type="button" class="in dpbtn" id="f_until_b" data-dp="f_until">'+(form.until?fmtD(form.until):'Optional: letzten Tag wählen')+'</button><input type="hidden" id="f_until" value="'+esc(form.until||'')+'"><p class="note" style="margin:6px 0 0">Bei unterschiedlichen Zeiten pro Tag: bitte in die Beschreibung schreiben.</p></div>':'<div class="field"><label>Mehrtägiges Event</label><div class="lockfield">Mehrtägige Events gibt es mit Plus.</div></div>')+
      '<div class="field"><label for="f_place">Location *</label><input class="in" id="f_place" value="'+esc(form.place)+'" placeholder="Name der Location"></div>'+
      '<div class="field"><label for="f_town">Ort *</label><input class="in" id="f_town" list="townlist" value="'+esc(form.town)+'" placeholder="z. B. Krems" autocomplete="off"><datalist id="townlist">'+TOWNS.map(function(t){return '<option value="'+esc(t)+'">'}).join('')+'</datalist></div>'+
      '<div class="field"><label for="f_cat">Kategorie *</label><select class="in" id="f_cat"><option value="">Kategorie wählen</option>'+CATS.map(function(c){return '<option'+(form.cat===c?' selected':'')+'>'+esc(c)+'</option>'}).join('')+'</select></div>'+
      '<div class="nav"><button class="btn ghost" id="back">Zurück</button><button class="btn" id="next">Weiter</button></div>';
    }else if(step===3){
      h=head('Mehr Details','Beschreibung, Preis, Link, Foto und Flyer.')+
      '<div class="field"><label for="f_desc">Beschreibung *</label><textarea class="in" id="f_desc" placeholder="Was erwartet die Gäste?">'+esc(form.desc)+'</textarea></div>'+
      '<div class="field"><label for="f_price">Eintritt *</label><div class="pricebox"><span>€</span><input class="in pin" id="f_price" inputmode="decimal" autocomplete="off" value="'+(form.price==='Gratis'&&form.priceSet?'0':pVal(form.price))+'" placeholder="0 = Eintritt frei"></div><small class="hint">0 = Eintritt frei.</small></div></div>'+((form.pack!=='free'||PROMO)?'<div class="field"><label for="f_link">Ticket-/Anmelde-Link (optional)</label><input class="in" id="f_link" inputmode="url" value="'+esc(form.link)+'" placeholder="https://"></div>':'<div class="field"><label>Ticket-/Anmelde-Link</label><div class="lockfield">Der Ticket-Button gehört zu Plus. Du kannst Plus in Schritt 1 wählen (aktuell gratis).</div></div>')+
      upl('f_img','img','Foto für die Eventansicht','JPG, PNG oder WebP. Ort, Essen oder Stimmung, bitte kein Flyer.',1)+upl('f_fly','fly','Flyer oder Plakat (optional)','Erscheint zusätzlich auf deiner Event-Seite. JPG, PNG oder WebP.',0)+'<div class="field"><label for="f_credit">Bildnachweis (Foto von) *</label><input class="in" id="f_credit" value="'+esc(form.credit)+'" placeholder="Name der Fotografin / des Fotografen"><small class="hint">Mit dem Einreichen bestätigst du, dass du die Rechte am Bild hast.</small></div>'+(form.ai?'<div class="fnote"><b>Dein Flyer ist gespeichert.</b> Er erscheint extra auf der Event-Seite.</div>':'')+
      '<div class="nav"><button class="btn ghost" id="back">Zurück</button><button class="btn" id="next">Weiter</button></div>';
    }else if(step===4){
      h=head('Wer lädt ein?','Für Rückfragen. Deine Adresse wird nicht veröffentlicht.')+
      '<div class="field"><label for="f_org">Veranstalter *</label><input class="in" id="f_org" value="'+esc(form.org)+'" placeholder="Name oder Verein"></div>'+
      '<div class="two"><div class="field"><label for="f_mail">E-Mail *</label><input class="in" id="f_mail" type="email" value="'+esc(form.mail)+'"></div><div class="field"><label for="f_tel">Telefon *</label><input class="in" id="f_tel" type="tel" value="'+esc(form.tel)+'"></div></div>'+
      '<p class="note">Du bist für Inhalt und Bildrechte deines Eintrags verantwortlich, nicht ewent. bzw. der Betreiber der Website.</p><p class="note">Wir prüfen jeden neuen Eintrag und schalten ihn meist innerhalb eines Tages frei.</p>'+
      '<div class="nav"><button class="btn ghost" id="back">Zurück</button><button class="btn accent" id="send">Einreichen</button></div>';
    }else{
      var p=PACKS.filter(function(x){return x.id===form.pack})[0];
      h='<div class="sheet-top"><span class="label">Eingegangen</span><button class="x" id="sx" aria-label="Schließen">×</button></div><div class="done"><div class="ok">✓</div><h2>Danke, wir schauen drüber.</h2><p class="note">„'+esc(form.title||'Dein Event')+'“ ist bei uns eingegangen. '+'Die Freigabe kommt per Mail.'+'</p>'+'<button class="btn block" id="fin">Fertig</button></div>';
    }
    s.innerHTML=h;
    var sx=$('#sx');if(sx)sx.onclick=function(){closeScrim('#submitScrim')};
    var n=$('#next');if(n)n.onclick=function(){save();if(step===2&&!need(['f_title','f_date','f_time','f_place','f_town','f_cat']))return;if(step===3&&!need(['f_desc','f_price','f_credit']))return;if(step===3&&!form.img){toast('Bitte lade ein Titelbild hoch.');setTimeout(function(){toast('')},3500);var q=$('#f_img');if(q)q.focus();return}step++;renderForm()};
    var b=$('#back');if(b)b.onclick=function(){save();step--;renderForm()};
    var f=$('#fin');if(f)f.onclick=function(){closeScrim('#submitScrim')};
    function bindUpl(id,key,max,q){var fi=$('#'+id);if(!fi)return;function take(f){if(!f)return;if(!/^image\/(jpeg|png|webp)$/.test(f.type)){toast('Bitte JPG, PNG oder WebP wählen.');setTimeout(function(){toast('')},3000);return}var im=new Image(),u=URL.createObjectURL(f);im.onload=function(){var w=im.width,h=im.height,k=Math.min(1,max/Math.max(w,h)),c=document.createElement('canvas');c.width=Math.round(w*k);c.height=Math.round(h*k);var x=c.getContext('2d');x.fillStyle='#fff';x.fillRect(0,0,c.width,c.height);x.drawImage(im,0,0,c.width,c.height);form[key]=c.toDataURL('image/jpeg',q);URL.revokeObjectURL(u);save();renderForm()};im.onerror=function(){toast('Das Bild konnte nicht gelesen werden.');setTimeout(function(){toast('')},3000)};im.src=u}
      fi.onchange=function(){take(fi.files&&fi.files[0]);fi.value=''};
      var z=fi.parentNode.querySelector('label.upl');if(z){['dragenter','dragover'].forEach(function(n){z.addEventListener(n,function(e){e.preventDefault();z.classList.add('drag')})});['dragleave','drop'].forEach(function(n){z.addEventListener(n,function(e){e.preventDefault();z.classList.remove('drag')})});z.addEventListener('drop',function(e){var f=e.dataTransfer&&e.dataTransfer.files&&e.dataTransfer.files[0];take(f)})}}
    bindUpl('f_img','img',1280,0.8);bindUpl('f_fly','fly',1600,0.85);
    s.querySelectorAll('[data-rm]').forEach(function(x){x.onclick=function(){form[x.dataset.rm]='';save();renderForm()}});
    var sd=$('#send');if(sd)sd.onclick=function(){save();if(!need(['f_org','f_mail','f_tel']))return;if(!/^\S+@\S+\.\S+$/.test(form.mail)){toast('Bitte eine gültige E-Mail angeben.');setTimeout(function(){toast('')},3500);return}if(form.pack==='free'&&!PROMO){form.link='';form.until=''}sd.disabled=true;sd.textContent='Wird gesendet …';post('submit',{title:form.title,date:form.date,until:form.until||'',time:form.time,tend:form.tend||'',place:form.place,town:form.town,cat:form.cat,desc:form.desc,price:pFmt(form.price),link:form.link,org:form.org,credit:form.credit||'',mail:form.mail,tel:form.tel,pack:form.pack,img:form.img||'',flyer:form.fly||''}).then(function(){step=5;renderForm()}).catch(function(er){sd.disabled=false;sd.textContent='Einreichen';toast(er.message);setTimeout(function(){toast('')},4000)})};
    s.querySelectorAll('[data-tp]').forEach(function(b){b.onclick=function(){tpOpen(b.dataset.tp)}});
    s.querySelectorAll('[data-dp]').forEach(function(b){b.onclick=function(){dpOpen(b.dataset.dp)}});
    s.querySelectorAll('[data-t]').forEach(function(b){b.onclick=function(){save();form.time=b.dataset.t;renderForm()}});
    var fcat=$('#fcat');if(fcat)CATS.forEach(function(c){fcat.appendChild(mk(c,form.cat===c,'cat',function(){save();form.cat=c;renderForm()}))});
    var ftown=$('#ftown');if(ftown)TOWNS.forEach(function(t){ftown.appendChild(mk(esc(t),form.town===t,'cat',function(){save();form.town=t;renderForm()}))});
    s.querySelectorAll('[data-p]:not([disabled])').forEach(function(c){c.onclick=function(){save();form.pack=c.dataset.p;renderForm()}});
  }
  ['dragover','drop'].forEach(function(n){window.addEventListener(n,function(e){if(!e.target.closest||!e.target.closest('label.upl'))e.preventDefault()})});
  document.querySelectorAll('[data-open-submit]').forEach(function(b){b.onclick=function(){openSubmit()}});
  document.addEventListener('click',function(ev){var b=ev.target.closest('[data-pack]');if(b)openSubmit(b.dataset.pack)});

  if($('#nlform'))$('#nlform').onsubmit=function(ev){ev.preventDefault();var f=this,m=$('#nlmail').value.trim(),s=$('#nlmsg');if(!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(m)){s.className='err';s.textContent='Bitte gib eine gültige E-Mail-Adresse ein.';return}s.className='';post('newsletter',{mail:m}).then(function(){f.innerHTML='<p class="note" style="margin:0">Danke, du bist auf der Liste. Die erste Mail kommt am nächsten Donnerstag.</p>';s.textContent=''}).catch(function(er){s.className='err';s.textContent=er.message})};
  var qc=$('#qclr');
  $('#q').addEventListener('input',function(){qc.hidden=!this.value});
  qc.onclick=function(){$('#q').value='';qc.hidden=true;F.q='';render();$('#q').focus()};
  document.addEventListener('click',function(ev){if(ev.target.closest('.empty [data-open-submit]'))openSubmit()});
  function toast(t){var el=$('#toast');if(t){el.textContent=t;el.hidden=false}else el.hidden=true}
  window.addEventListener('offline',function(){toast('Keine Verbindung. Du siehst den letzten Stand.')});
  window.addEventListener('online',function(){toast('');render()});
  function skel(){renderQuick();$('#list').innerHTML='<div class="section-head"><h2>Alle Veranstaltungen</h2></div>'+'<div class="sk"><i></i><b></b><b></b></div>'.repeat(2)}

  render();
  if(location.hash==='#submit'){openSubmit()}
})();