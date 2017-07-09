function saveInLocalStorage(page)
{
    localStorage.setItem(page, document.location.search);
}

function getInLocalStorage(page)
{
    var filtro =  localStorage.getItem(page);

    if ( ! (filtro != null && filtro != undefined) ) {
        filtro = '';
    }

    document.getElementById("listado").href = document.getElementById("listado").href +  filtro;
}

function stringIsEmpty(str)
{
    return (! str || str.length === 0);
}
