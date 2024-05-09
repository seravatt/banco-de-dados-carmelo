
const estados = [
    { id: 1, name: "Acre" },
  { id: 2, name: "Alagoas" },
  { id: 3, name: "Amazonas" },
  { id: 4, name: "Amapá" },
  { id: 5, name: "Bahia" },
  { id: 6, name: "Ceará" },
  { id: 7, name: "Distrito Federal" },
  { id: 8, name: "Espírito Santo" },
  { id: 9, name: "Goiás" }, 
  { id: 11, name: "Maranhão" },
  { id: 12, name: "Mato Grosso" },
  { id: 13, name: "Mato Grosso do Sul" },
  { id: 14, name: "Minas Gerais" },
  { id: 15, name: "Pará" },
  { id: 16, name: "Paraíba" },
  { id: 17, name: "Paraná" },
  { id: 18, name: "Pernambuco" },
  { id: 19, name: "Piauí" },
  { id: 20, name: "Rio de Janeiro" },
  { id: 21, name: "Rio Grande do Norte" },
  { id: 22, name: "Rio Grande do Sul" },
  { id: 23, name: "Rondônia" },
  { id: 24, name: "Roraima" },
  { id: 25, name: "Santa Catarina" },
  { id: 26, name: "São Paulo" },
  { id: 27, name: "Sergipe" },
  { id: 28, name: "Tocantins" },
]

const cidades = [
    { id: 1, name: "Rio Branco", stateId: 1 },
    { id: 2, name: "Maceió", stateId: 2 },
    { id: 3, name: "Manaus", stateId: 3 },
   
];


function populateStateSelects() {
    const estadoOrigemSelect = document.getElementById('estado-origem');
    const estadoDestinoSelect = document.getElementById('estado-destino');

    estados.forEach(estado => {
        const option = document.createElement('option');
        option.value = estado.id;
        option.text = estado.name;
        estadoOrigemSelect.appendChild(option.cloneNode(true));
        estadoDestinoSelect.appendChild(option);
    });
}


function updateCityOptions(selectElement, stateId) {
    const filteredCities = cidades.filter(cidade => cidade.stateId === stateId);
    selectElement.innerHTML = '<option value="">Selecione uma Cidade</option>';

    filteredCities.forEach(cidade => {
        const option = document.createElement('option');
        option.value = cidade.id;
        option.text = cidade.name;
        selectElement.appendChild(option);
    });
}


window.onload = () => {
    populateStateSelects();

    const estadoOrigemSelect = document.getElementById('estado-origem');
    const estadoDestinoSelect = document.getElementById('estado-destino');
    const cidadeOrigemSelect = document.getElementById('cidade-origem');
    const cidadeDestinoSelect = document.getElementById('cidade-destino');

    estadoOrigemSelect.addEventListener('change', () => {
        updateCityOptions(cidadeOrigemSelect, estadoOrigemSelect.value);
    });

    estadoDestinoSelect.addEventListener('change', () => {
        updateCityOptions(cidadeDestinoSelect, estadoDestinoSelect.value);
    });
};
