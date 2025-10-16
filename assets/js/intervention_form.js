
// Sélection du conteneur
const collectionHolder = document.getElementById('documents-wrapper');
const addButton = document.getElementById('add-document');

// Compteur basé sur le nombre d’items existants
collectionHolder.dataset.index = collectionHolder.querySelectorAll('.document-item').length;

addButton.addEventListener('click', () => {
    const prototype = collectionHolder.dataset.prototype;
    const index = collectionHolder.dataset.index;

    // Remplace les __name__ par le bon index
    const newForm = prototype.replace(/__name__/g, index);
    collectionHolder.dataset.index++;

    // Création de l'élément DOM
    const item = document.createElement('div');
    item.classList.add('document-item', 'border', 'rounded', 'p-3', 'mb-3');
    item.innerHTML = newForm + '<button type="button" class="btn btn-danger btn-sm remove-document">Supprimer</button>';

    collectionHolder.appendChild(item);
});

// Gestion du bouton "Supprimer"
document.addEventListener('click', (e) => {
    if (e.target && e.target.classList.contains('remove-document')) {
        e.target.closest('.document-item').remove();
    }

})

// le charger sur intervention/new.html.twig avec la ligne: <script src="{{ asset('build/intervention_form.js') }}"></script>