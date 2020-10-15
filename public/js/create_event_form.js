console.log('coucou !');

const citySelect = document.getElementById('event_city');
const locationSelect = document.getElementById('event_location');
const modalElement = document.getElementById('location-form-container');
const openModalButton = document.getElementById("open-location-modal-button");
const closeModalButton = document.getElementById("close-modal-button");

let lastLocations = [];

function onCityChange(e){
    let selectedCityId = e.currentTarget.value;

    fetch(ROOT_URL + "api/v1/city/"+selectedCityId+"/locations").then(function(response){
            return response.json();
        })
        .then(function(city){
            let selectOptions = "";
            city.locations.forEach(location => {
                lastLocations.push(location);
                selectOptions += '<option value="'+location.id+'">'+location.name+'</option>';
            });
            locationSelect.innerHTML = selectOptions;
        });
}

function onLocationChange(e){
    let selectedLocationData = {};
    let selectedLocationId = e.currentTarget.value;
    for(let i = 0; i < lastLocations.length; i++){
        if (lastLocations[i].id == selectedLocationId){
            selectedLocationData = lastLocations[i];
        }
    }

    console.log(selectedLocationData);
    document.getElementById("street_address").innerHTML = selectedLocationData.street_number + " " + selectedLocationData.street_name;
    document.getElementById("zip").innerHTML = selectedLocationData.zip;
    document.getElementById("lat").innerHTML = selectedLocationData.lat;
    document.getElementById("lng").innerHTML = selectedLocationData.lng;

}

function openModal(){
    console.log("open");
    console.log(modalElement.classList);
    modalElement.classList.remove('hidden');
}

function closeModal(){
    console.log("close");
    console.log(modalElement.classList);

    modalElement.classList.add('hidden');
}

citySelect.addEventListener('change', onCityChange);
locationSelect.addEventListener('change', onLocationChange);
openModalButton.addEventListener('click', openModal);
closeModalButton.addEventListener('click', closeModal);