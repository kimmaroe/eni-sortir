console.log('coucou !');

const citySelect = document.getElementById('event_city');
const locationSelect = document.getElementById('event_location');
const modalElement = document.getElementById('location-form-container');
const openModalButton = document.getElementById("open-location-modal-button");
const closeModalButton = document.getElementById("close-modal-button");
const createLocationForm = document.getElementById("create-location-form");
const createLocationErrorsContainer = document.getElementById("create-location-errors");
const locationCitySelect = document.getElementById('location_city');

let lastLocations = [];

//récupère les lieux associés à une ville (cityId)
//on peut lui passer un id de ville et un id de lieu en 2e et 3e
//ceux-ci seront alors "préselectionné" dans la liste déroulante
function getCityLocations(cityId, cityIdToSelect, locationIdToSelect){
    fetch(ROOT_URL + "api/v1/city/"+cityId+"/locations").then(function(response){
        return response.json();
    })
        .then(function(city){
            let selectOptions = "";
            city.locations.forEach(location => {
                lastLocations.push(location);
                selectOptions += '<option value="'+location.id+'">'+location.name+'</option>';
            });
            locationSelect.innerHTML = selectOptions;

            //sélectionne la bonne ville dans le form
            if (cityIdToSelect) {
                let cityToSelect = citySelect.querySelector('option[value="' + cityIdToSelect + '"]');
                cityToSelect.selected = "selected";
            }

            //ajoute l'option dans la liste des lieux et la sélectionne
            if (locationIdToSelect) {
                let locationToSelect = locationSelect.querySelector('option[value="' + locationIdToSelect + '"]');
                locationToSelect.selected = "selected";
                showLocationInfos(locationIdToSelect);
            }
        });
}

//affiche l'adresse du lieu dans les div
function showLocationInfos(locationId){
    let selectedLocationData = {};
    for(let i = 0; i < lastLocations.length; i++){
        if (lastLocations[i].id == locationId){
            selectedLocationData = lastLocations[i];
        }
    }

    //injecte les infos dans les div
    document.getElementById("street_address").innerHTML = selectedLocationData.street_number + " " + selectedLocationData.street_name;
    document.getElementById("zip").innerHTML = selectedLocationData.zip;
    document.getElementById("lat").innerHTML = selectedLocationData.lat ? selectedLocationData.lat : "";
    document.getElementById("lng").innerHTML = selectedLocationData.lng ? selectedLocationData.lng : "";

}

//ouvre la modale pour créer un lieu
function openModal(){
    //sélectionne la bonne ville dans la modale
    locationCitySelect.querySelector('option[value="'+citySelect.value+'"]').selected = "selected";
    modalElement.classList.remove('hidden');
}

//ferme la modale
function closeModal(){
    modalElement.classList.add('hidden');
}

//appelée sur soumission du formulaire de création de lieu
//envoie les données en ajax
function onSubmitCreateLocationForm(e)
{
    //empêche la soumission réelle du formulaire
    e.preventDefault();

    //vide les erreurs de validation au cas où
    createLocationErrorsContainer.innerHTML = "";

    //crée un objet contenant les données du form
    data = new URLSearchParams(new FormData(e.currentTarget));

    //envoie ces données en post à l'API
    //l'action est renseignée sur la balise form, pour se faciliter la vie ici avec l'URL
    fetch(e.currentTarget.action, {
        method: 'post',
        body: data,
    }).then(function(response){
            //si erreurs de validation....
            if (response.status === 400){
                response.json().then(function(response){
                    //tout ça pour afficher les erreurs de validation...
                    createLocationErrorsContainer.innerHTML = "";
                    for(key in response.form.children){
                        if (!response.form.children.hasOwnProperty(key)){
                            continue;
                        }
                        let field = response.form.children[key];
                        if (undefined !== field.errors) {
                            for (let k = 0; k < field.errors.length; k++) {
                                let errorLi = document.createElement('li');
                                errorLi.innerHTML = field.errors[k];
                                createLocationErrorsContainer.appendChild(errorLi);
                            }
                        }
                    }
                })
            }
            //si l'ajout a bien marché
            else {
                response.json().then(function(response){
                    //on refait une requête ajax pour récupérer les nouveaux lieux de cette ville
                    getCityLocations(response.city.id, response.city.id, response.id);
                    //ferme la modale
                    closeModal();
                })
            }
        });

}

//quand la valeur change dans la liste déroulante des villes...
citySelect.addEventListener('change', function(e){
    //on va chercher les lieux de la ville choisie
    let selectedCityId = e.currentTarget.value;
    getCityLocations(selectedCityId);
});

//quand on change un lieu...
locationSelect.addEventListener('change', function(e){
    //on affiche son adresse
    showLocationInfos(e.currentTarget.value);
});

//ouvre ou ferme la modele sur click
openModalButton.addEventListener('click', openModal);
closeModalButton.addEventListener('click', closeModal);

//appelle ma fonction quand le form de création de lieu est soumis
createLocationForm.addEventListener('submit', onSubmitCreateLocationForm);