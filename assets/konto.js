(function(){
  var f=document.getElementById('kform');if(!f)return;
  f.onsubmit=function(ev){ev.preventDefault();
    var m=document.getElementById('k_mail').value.trim(),o=document.getElementById('k_msg'),b=f.querySelector('button');
    if(!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(m)){o.style.color='#A3402A';o.textContent='Bitte gib eine gültige E-Mail-Adresse ein.';return}
    b.disabled=true;o.style.color='';
    fetch(EWENT_KONTO.rest+'login',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({mail:m})})
      .then(function(r){return r.json().then(function(j){if(!r.ok)throw new Error(j.message||'Das hat leider nicht geklappt.');return j})})
      .then(function(){f.innerHTML='<p class="note"><b>Schau in dein Postfach.</b> Wenn zu dieser Adresse ein Event hinterlegt ist, haben wir dir einen Link geschickt. Er gilt 15 Minuten.</p>'})
      .catch(function(e){b.disabled=false;o.style.color='#A3402A';o.textContent=e.message});
  };
})();
