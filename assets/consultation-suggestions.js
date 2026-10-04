// These prompts are displayed for nurse review only; they are never saved as care given.
window.clinicConsultationSuggestions = function (selectedKeys) {
  const options = {
    fever: { symptom: 'Fever', medicine: 'Paracetamol', note: 'Consider only if uncomfortable; check age, weight, allergies and clinic protocol.' },
    headache: { symptom: 'Headache', medicine: 'Paracetamol', note: 'Review the cause and check suitability before use.' },
    has_headache: { symptom: 'Headache', medicine: 'Paracetamol', note: 'Review the cause and check suitability before use.' },
    cough: { symptom: 'Cough', medicine: 'Honey-based cough preparation', note: 'Check age and symptoms; routine cough medicines may be unsuitable for children.' },
    colds: { symptom: 'Colds', medicine: 'Saline nasal drops or spray', note: 'Avoid routine decongestants without checking age and clinic protocol.' },
    sore_throat: { symptom: 'Sore throat', medicine: 'Paracetamol', note: 'For pain only after checking suitability; assess persistent or severe symptoms.' },
    stomachache: { symptom: 'Stomachache', medicine: 'Antacid only if indigestion is assessed', note: 'Check the cause and age suitability first; unexplained pain needs review.' }
  };
  return selectedKeys.filter(key => options[key]).map(key => options[key]);
};
