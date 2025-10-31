$(document).ready(function(){
  $('.cellphone').mask('(00) 00000-0000');
  $('.phone').mask('(00) 0000-0000');

  const hoje = new Date(); // Guarda a data com o fuso horário do servidor

  //Ajuste do fuso para o fuso local
  const diferencaFuso = hoje.getTimezoneOffset(); // Pega a diferença do fuso em minutos para o UTC
  hoje.setMinutes(hoje.getMinutes() - diferencaFuso); // Pega os minutos do fuso do servidor e diminui com a diferença

  // Transforma a data no formato YYYY-MM-DD e pega somente a primeira parte, descartando o horário
  const dataLocal = hoje.toISOString().split('T')[0]; 

  document.getElementById('birth_date').setAttribute('max', dataLocal);
});