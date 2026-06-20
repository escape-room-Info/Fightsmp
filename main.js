// main.js
const SYSTEM=`Du bist der freundliche Support-Bot von FightSMP...`; // Dein bestehender System-Prompt

let history=[];
let chatOpen=false;

function toggleChat(){
  chatOpen=!chatOpen;
  document.getElementById('chat-window').classList.toggle('open',chatOpen);
  document.getElementById('chat-notif').style.display='none';
  if(chatOpen)document.getElementById('chat-input').focus();
}

function sendSug(text){
  document.getElementById('suggestions').style.display='none';
  document.getElementById('chat-input').value=text;
  sendMsg();
}

async function sendMsg(){ /* Dein bestehender Code */ }
function addMsg(text,cls){ /* Dein bestehender Code */ }

function copyIP(){
  navigator.clipboard.writeText('play.fightsmp.net').then(()=>{
    const el=document.getElementById('ip-btn');
    el.textContent='✓ Kopiert!';
    setTimeout(()=>el.textContent='⚔ play.fightsmp.net',1800);
  });
}

// Modal Funktionen
let currentRank='',currentPrice='';
function openModal(name,price){
  currentRank=name;currentPrice=price;
  document.getElementById('modal-name').textContent=name;
  document.getElementById('modal-price').textContent=price.replace('.',',')+' €';
  document.getElementById('modal').classList.add('open');
}
function closeModal(){document.getElementById('modal').classList.remove('open');}
function closeModalBg(e){if(e.target===document.getElementById('modal'))closeModal();}
function checkout(method){ /* Dein bestehender Checkout-Code */ }
function toggleFaq(el){ /* Dein bestehender FAQ-Code */ }

// Animated player count (Angepasst für Multi-Page)
(function(){
  const countEl = document.getElementById('player-count');
  const barEl = document.getElementById('bar');
  
  // Führt den Code nur aus, wenn die Elemente existieren (also nur auf der Startseite)
  if(!countEl || !barEl) return; 

  const target=Math.floor(Math.random()*45)+8;
  let i=0;
  const t=setInterval(()=>{
    i++;
    countEl.textContent=i+'/100';
    barEl.style.width=i+'%';
    if(i>=target)clearInterval(t);
  },35);
})();
