const $=s=>document.querySelector(s); const show=(el,text)=>{el.classList.remove('hidden');el.textContent=text};
async function post(url,data){const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});const result=await r.json();if(!r.ok||result.ok===false)throw Error(result.error||`Request failed (HTTP ${r.status})`);return result}
function formData(form){return Object.fromEntries(new FormData(form).entries())}
function bindAI(formId,mode,resultId){
  const f=$(formId); if(!f)return;
  f.addEventListener('submit',async e=>{
    e.preventDefault();
    const box=$(resultId);show(box,'Thinking locally…');
    try{
      const d=await post('/api/ai/'+mode,formData(f));
      if(!d.ok)throw Error(d.error||'AI unavailable');
      box.textContent=d.text||'No plan returned.';
      if(mode==='outing'){
        const i=formData(f);
        await post('/api/outings',{...i,title:i.activity+' mission',ai_plan:d.text});
      }
    }catch(err){
      const message=err.message||'Unknown local AI error';
      box.textContent=/timed out|timeout/i.test(message)
        ? 'Local AI took too long to finish. Your mission was not saved. Try again with shorter notes or a smaller local model.\n\n'+message
        : 'Local AI could not complete this request. Check that Ollama is running and the model is available.\n\n'+message;
    }
  });
}
bindAI('#outingForm','outing','#outingResult');bindAI('#gardenForm','garden','#gardenResult');bindAI('#birdingForm','birding','#birdingResult');
$('#logForm')?.addEventListener('submit',async e=>{e.preventDefault();const box=$('#logResult');try{await post('/api/logs',formData(e.target));show(box,'Observation saved locally.');e.target.reset()}catch(err){show(box,'Could not save observation: '+err.message)}});
$('#demoCaptureForm')?.addEventListener('submit',e=>{e.preventDefault();const d=formData(e.target);const summary=`<h3>${d.mission || 'Mission'} </h3><p><strong>Activity:</strong> ${d.activity || 'walk'}</p><p><strong>Location:</strong> ${d.location || 'local park'}</p><p><strong>Duration:</strong> ${d.duration || 45} minutes</p><p><strong>Notes:</strong> ${d.notes || 'No notes entered yet.'}</p><p><strong>Status:</strong> Ready for the field</p>`;$('#demoOutput').innerHTML=summary;$('#demoTitle').textContent=`${d.mission || 'Mission'} · ${d.duration || 45} minutes`;$('#demoSubtitle').textContent=`${d.activity || 'walk'} route planned for ${d.location || 'local park'}.`;
const voiceText=`Mission captured. ${d.activity || 'Walk'} at ${d.location || 'local park'} for ${d.duration || 45} minutes. ${d.notes || 'Ready for the field.'}`;window.speechSynthesis?.cancel(); if ('speechSynthesis' in window) { const utterance=new SpeechSynthesisUtterance(voiceText); utterance.rate=0.92; utterance.pitch=1.1; window.speechSynthesis.speak(utterance);} });
const voiceDemoScript='TouchGrass AI is creating a local mission. The route is saved to SQLite on this device. The AI plan is ready, the field note is stored, and the next step is to step outside and leave the screen behind.';
const voiceDemoBtn=$('#voiceDemoBtn'); const stopVoiceBtn=$('#stopVoiceBtn');
voiceDemoBtn?.addEventListener('click',()=>{
  if (!('speechSynthesis' in window)) {
    $('#demoSubtitle').textContent='This browser does not support built-in voice output.';
    return;
  }
  window.speechSynthesis.cancel();
  const utterance=new SpeechSynthesisUtterance(voiceDemoScript);
  utterance.rate=0.94;
  utterance.pitch=1.1;
  utterance.onstart=()=>$('#demoMeta').textContent='TouchGrass AI · voice demo active';
  utterance.onend=()=>$('#demoMeta').textContent='TouchGrass AI · local mode';
  window.speechSynthesis.speak(utterance);
});
stopVoiceBtn?.addEventListener('click',()=>{window.speechSynthesis?.cancel();$('#demoMeta').textContent='TouchGrass AI · local mode';});
document.querySelectorAll('.chip').forEach(chip=>chip.addEventListener('click',()=>{document.querySelectorAll('.chip').forEach(c=>c.classList.toggle('active',c===chip));const filter=chip.dataset.filter||'all';document.querySelectorAll('.roadmap-card').forEach(card=>{const match=filter==='all'||card.dataset.group===filter;card.style.display=match?'block':'none';});}));
$('#locateBtn')?.addEventListener('click',()=>navigator.geolocation?.getCurrentPosition(p=>{const s=`Approximate location available: ${p.coords.latitude.toFixed(3)}, ${p.coords.longitude.toFixed(3)}. Use it only if you want to paste the area into the form.`;$('#locationStatus').textContent=s},()=>$('#locationStatus').textContent='Location permission was not granted. You can enter an area manually.'));
if('serviceWorker' in navigator)navigator.serviceWorker.register('/sw.js').catch(()=>{}); $('#offlineBadge').textContent=navigator.onLine?'Online shell • AI can still be local':'Offline mode • local shell available'; window.addEventListener('offline',()=>$('#offlineBadge').textContent='Offline mode • local shell available'); window.addEventListener('online',()=>$('#offlineBadge').textContent='Online shell • AI can still be local');
