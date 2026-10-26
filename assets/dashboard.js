'use strict';
// Os números continuam disponíveis no HTML se o CDN estiver indisponível.
if (typeof Chart !== 'undefined') {
 const dados = JSON.parse(document.getElementById('dados-graficos').textContent);
 Chart.defaults.font.family = 'system-ui, sans-serif';
 new Chart(document.getElementById('idades'), {type:'bar', data:{labels:dados.salas,datasets:[{label:'Média de idade',data:dados.medias,backgroundColor:'#002366',borderRadius:7}]},options:{responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true}},plugins:{legend:{display:false}}}});
 new Chart(document.getElementById('doacoes'), {type:'doughnut',data:{labels:['Doaram','Ainda não doaram'],datasets:[{data:dados.doacoes,backgroundColor:['#002366','#cbdaf4'],borderWidth:4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}}}});
}
