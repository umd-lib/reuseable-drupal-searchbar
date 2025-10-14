// Fill in form values from arg

const queryString = window.location.search;
const urlParams = new URLSearchParams(queryString);
const searchStr = urlParams.get('query');
if (searcherStr) {
  document.getElementById("edit-reusable-searchbar").value = searcherStr;
}
