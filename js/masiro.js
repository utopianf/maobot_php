var masiro = new Array();
masiro = "<?= $result ?>";
masiro = shuffle(masiro);
for (var i = 0; i <= masiro.length - 1; i++) {
	document.write("<a class='example-image-link' href='img/masiro/" + masiro[i] + "' data-lightbox='example-set' data-title='Click'><img class='example-image' src='img/masiro/" + masiro[i] + "' alt=''/></a>");
}
function shuffle(array) {
	var m = array.length, t, i;
	while (m) {
		i = Math.floor(Math.random() * m--);
		t = array[m];
		array[m] = array[i];
		array[i] = t;
	}
	return array;
}
