$
function convertFormToJSON(form) {
  return form
    .serializeArray()
    .reduce(function (json, { name, value }) {
	  Object.assign(json, {[name]: value});
      return json;
    }, {});
}