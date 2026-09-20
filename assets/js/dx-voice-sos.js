/**
 * DEPENDEX.SOCIAL — AI MAIEUTICA VOCALE SOS & ASCOLTO EMPATICO
 * Assistente vocale a bassa latenza su browser nativo (Web Speech Recognition + SpeechSynthesis).
 * Rispetta le direttive deontologiche di HUMAN_WELFARE_OS_4.md:
 * - Zero diagnosi cliniche, zero giudizio o colpevolizzazione.
 * - De-escalation emotiva immediata, ascolto maieutico e invito alla respirazione/comunità.
 * - 100% On-Device: nessun invio audio a server di terze parti.
 */

(function() {
  'use strict';

  const MAIEUTIC_RESPONSES = [
    {
      keywords: ['paura', 'ansia', 'panico', 'angoscia', 'agitato', 'agitata', 'tremore'],
      response: "Ti ascolto e sento quello che stai provando. È un momento difficile, ma non sei solo. Fermati un secondo, poggia i piedi a terra e fai un respiro profondo con me. Guarda il cerchio azzurro e segui il suo ritmo."
    },
    {
      keywords: ['bere', 'ricaduta', 'voglia', 'alcol', 'sento che cedo', 'cedere', 'tentazione'],
      response: "La voglia è un'onda. Non puoi fermare il mare, ma puoi imparare a navigare quest'onda per i prossimi dieci minuti. Bevi un bicchiere d'acqua fresca, respira lentamente e chiama subito il Telefono Verde gratuito 800 632 000, o trova il cerchio del Club vicino a te stasera."
    },
    {
      keywords: ['solo', 'sola', 'abbandonato', 'abbandonata', 'nessuno mi capisce', 'triste', 'solitudine'],
      response: "Nei Club Multifamiliari ci sono persone che hanno attraversato esattamente questa solitudine e sono pronte ad ascoltarti senza giudizio. La tua presenza ha valore. Se vuoi, tocca il pulsante verde e parla direttamente con un servitore-insegnante del Club."
    },
    {
      keywords: ['famiglia', 'moglie', 'marito', 'figlio', 'figlia', 'mamma', 'papà', 'litigio', 'casa'],
      response: "La famiglia vive insieme la fatica, ma può vivere insieme anche la rinascita. Nel Club la sedia è aperta a tutta la famiglia, insieme. Non serve convincere nessuno oggi: puoi iniziare tu a fare un piccolo passo."
    }
  ];

  const DEFAULT_RESPONSE = "Ti ascolto con rispetto e senza giudizio. Respira con calma. Qualunque cosa tu stia vivendo in questo istante, possiamo affrontarla insieme un passo alla volta. Vuoi che ti aiuti a trovare il Club più vicino a te o preferisci fare qualche respiro profondo?";

  let recognition = null;
  let isListening = false;
  let isSpeaking = false;

  // Inizializzazione Riconoscimento Vocale
  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (SpeechRecognition) {
    recognition = new SpeechRecognition();
    recognition.lang = 'it-IT';
    recognition.continuous = false;
    recognition.interimResults = false;

    recognition.onstart = function() {
      isListening = true;
      updateVoiceUiState('listening');
    };

    recognition.onresult = function(event) {
      isListening = false;
      const transcript = event.results[0][0].transcript;
      handleUserInput(transcript);
    };

    recognition.onerror = function(event) {
      isListening = false;
      updateVoiceUiState('idle');
      console.warn('[VoiceSOS] Errore microfono:', event.error);
    };

    recognition.onend = function() {
      isListening = false;
      if (!isSpeaking) {
        updateVoiceUiState('idle');
      }
    };
  }

  function handleUserInput(text) {
    if (!text || !text.trim()) return;
    showTranscript(text, 'user');

    const lower = text.toLowerCase();
    let reply = DEFAULT_RESPONSE;

    for (let r of MAIEUTIC_RESPONSES) {
      if (r.keywords.some(kw => lower.includes(kw))) {
        reply = r.response;
        break;
      }
    }

    setTimeout(function() {
      speakMaieuticResponse(reply);
    }, 400);
  }

  function speakMaieuticResponse(replyText) {
    showTranscript(replyText, 'assistant');

    if (!('speechSynthesis' in window)) {
      updateVoiceUiState('idle');
      return;
    }

    window.speechSynthesis.cancel();
    const utterance = new SpeechSynthesisUtterance(replyText);
    utterance.lang = 'it-IT';
    utterance.rate = 0.92; // Tono calmo, rilassato e rassicurante
    utterance.pitch = 1.0;

    // Seleziona voce italiana disponibile
    const voices = window.speechSynthesis.getVoices();
    const itVoice = voices.find(v => v.lang.startsWith('it'));
    if (itVoice) {
      utterance.voice = itVoice;
    }

    utterance.onstart = function() {
      isSpeaking = true;
      updateVoiceUiState('speaking');
    };

    utterance.onend = function() {
      isSpeaking = false;
      updateVoiceUiState('idle');
    };

    utterance.onerror = function() {
      isSpeaking = false;
      updateVoiceUiState('idle');
    };

    window.speechSynthesis.speak(utterance);
  }

  function showTranscript(text, sender) {
    const box = document.getElementById('dxVoiceTranscriptBox');
    if (!box) return;
    box.style.display = 'block';

    const p = document.createElement('div');
    p.style.cssText = sender === 'user'
      ? 'background: rgba(0, 240, 255, 0.12); color: #00f0ff; border-radius: 10px; padding: 8px 12px; margin-bottom: 6px; font-size: 0.85rem; text-align: left;'
      : 'background: rgba(255, 215, 0, 0.12); color: #ffd700; border-radius: 10px; padding: 8px 12px; margin-bottom: 6px; font-size: 0.88rem; text-align: left; line-height: 1.45;';
    p.innerHTML = sender === 'user' ? '<b>Tu:</b> ' + escapeHtml(text) : '<b>Ascolto Empatico:</b> ' + escapeHtml(text);
    box.appendChild(p);
    box.scrollTop = box.scrollHeight;
  }

  function updateVoiceUiState(state) {
    const btn = document.getElementById('dxVoiceSosBtn');
    const statusTxt = document.getElementById('dxVoiceStatusTxt');
    if (!btn) return;

    if (state === 'listening') {
      btn.style.background = 'radial-gradient(circle, #ef4444 0%, #b91c1c 100%)';
      btn.style.boxShadow = '0 0 25px rgba(239, 68, 68, 0.7)';
      btn.innerHTML = '<span style="font-size:1.2rem;">&#127908;</span> <span>Ti ascolto... Parla pure</span>';
      if (statusTxt) statusTxt.textContent = 'Ascolto attivo. Dì pure come ti senti...';
    } else if (state === 'speaking') {
      btn.style.background = 'radial-gradient(circle, #10b981 0%, #047857 100%)';
      btn.style.boxShadow = '0 0 25px rgba(16, 185, 129, 0.7)';
      btn.innerHTML = '<span style="font-size:1.2rem;">&#128266;</span> <span>Ascolto Empatico in corso</span>';
      if (statusTxt) statusTxt.textContent = 'Ascolta la risposta calma...';
    } else {
      btn.style.background = 'linear-gradient(135deg, #00f0ff, #0077ff)';
      btn.style.boxShadow = '0 0 20px rgba(0, 240, 255, 0.4)';
      btn.innerHTML = '<span style="font-size:1.2rem;">&#127908;</span> <span>Parla con me (Ascolto Vocale)</span>';
      if (statusTxt) statusTxt.textContent = 'Tocca il microfono per parlare a voce libera';
    }
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
  }

  // API Pubbliche
  window.startVoiceSos = function() {
    if (isSpeaking) {
      window.speechSynthesis.cancel();
      isSpeaking = false;
      updateVoiceUiState('idle');
      return;
    }

    if (isListening && recognition) {
      recognition.stop();
      isListening = false;
      updateVoiceUiState('idle');
      return;
    }

    if (recognition) {
      try {
        recognition.start();
      } catch (err) {
        console.warn('[VoiceSOS] Errore avvio:', err);
      }
    } else {
      // Fallback input testuale se microfono non supportato dal browser
      const userText = prompt('La registrazione vocale non è supportata dal browser. Scrivi qui come ti senti:');
      if (userText) {
        handleUserInput(userText);
      }
    }
  };

  window.sendVoiceTextFallback = function(text) {
    handleUserInput(text);
  };

})();
