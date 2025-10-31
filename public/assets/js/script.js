$(document).ready(function(){
  $('.cellphone').mask('(00) 00000-0000');
  $('.phone').mask('(00) 0000-0000');

  const hoje = new Date();

  //Ajuste do fuso para o fuso local
  const diferencaFuso = hoje.getTimezoneOffset(); //diferença do fuso em minutos para o UTC
  hoje.setMinutes(hoje.getMinutes() - diferencaFuso);

  const dataLocal = hoje.toISOString().split('T')[0];

  document.getElementById('birth_date').setAttribute('max', dataLocal);
});