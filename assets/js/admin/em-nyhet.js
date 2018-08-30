(() => {

	console.log(nyhet_meta);

	let newtype = '';

	// meta box
	let container = document.querySelector('.nyhet-meta-container');

	// new div helper function
	let newdiv = (o = {}) => {
		let div = document.createElement('div');

		if (o.class) {
			if (Array.isArray(o.class))
				for (let c of o.class)
					div.classList.add(c);
			else
				div.classList.add(o.class);
		}

		if (o.text) div.appendChild(document.createTextNode(o.text));

		return div;
	}

	// new input helper function
	let newinput = (o = {}) => {
		if (!o.name) return document.createElement('div');

		let container = newdiv({class: 'nyhet-input-container'});

		let title = newdiv({class: 'nyhet-input-title', text: o.title});
		container.appendChild(title);

		let input = document.createElement('input');

		if (!o.type) input.setAttribute('type', 'text');
		else input.setAttribute('type', o.type);

		if (!o.sort) input.setAttribute('value', (nyhet_meta.meta[o.name] == undefined) ? '' : nyhet_meta.meta[o.name]);
		else {
			let sort = nyhet_meta.nyhet_sort;

			if (o.sort != 'default') sort = nyhet_meta['nyhet_sort_'+o.sort];

			if (sort == undefined) sort = nyhet_meta.nyhet_sort;

			input.setAttribute('value', sort);
		}

		if (o.step) input.setAttribute('step', parseFloat(o.step));
		if (o.max) input.setAttribute('max', parseFloat(o.step));
		if (o.min) input.setAttribute('min', parseFloat(o.step));


		console.log(o.name);

		if (!o.notData) input.setAttribute('name', 'nyhet_data['+o.name+']');
		else input.setAttribute('name', o.name);

		container.appendChild(input);


		return container;
	}

	// creating the drop down for dice selection
	let dicedropdown = (o = {}) => {
		let container = document.createElement('div');

		let input = document.createElement('select');
		input.setAttribute('name', 'nyhet_data[terning]');

		container.appendChild(newdiv({class: 'nyhet-input-title', text: 'Terningkast'}));

		// helper function for creating option tag
		let addOption = (o = {}) => {
			let option = document.createElement('option');
			option.setAttribute('value', o.value);
			if (o.value == nyhet_meta.meta.terning) option.setAttribute('selected', '');
			option.appendChild(document.createTextNode(o.value));
			return option;
		}

		// adding option tags
		let v = ['ingen', 'en', 'to', 'tre', 'fire', 'fem', 'seks'];
		for (let i of v)
			input.appendChild(addOption({value: i}));

		container.appendChild(input);

		return container; 
	}

	let container_sort = newdiv({class: 'nyhet-sort-container'});
	container_sort.appendChild(newinput({
		name: 'nyhet_sort', 
		title: 'Sortering', 
		notData: true, 
		sort: 'default', 
		type: 'number',
		step: 0.01
	}));

	container.appendChild(container_sort);

	for (let sort of nyhet_meta['tax']) {
		// console.log(sort);
		// console.log(unescape(decodeURIComponent(sort)));
		container_sort.appendChild(newinput({
			name: 'nyhet_sort_'+sort, 
			title: 'Sortering '+sort.replace(/-/g, ' '), 
			notData: true, 
			// sort: decodeURIComponent(escape(sort)), 
			sort: sort, 
			type: 'number',
			step: 0.01
		}));
	}

	// container.appendChild(newinput({name: 'readmore', title: 'Read More Link'}));

	container.appendChild(newinput({name: 'title', title: 'Title for shortcode'}));
	container.appendChild(newinput({name: 'text', title: 'Tekst for shortcode'}));

	// let info_container = newdiv({class: 'nyhet-info-container'});

	// info_container.appendChild(newinput({name: 'info01', title: 'Text 01'}));
	// info_container.appendChild(newinput({name: 'info05', title: 'Text 05'}));
	// info_container.appendChild(newinput({name: 'info02', title: 'Text 02'}));
	// info_container.appendChild(newinput({name: 'info06', title: 'Text 06'}));
	// info_container.appendChild(newinput({name: 'info03', title: 'Text 03'}));
	// info_container.appendChild(newinput({name: 'info07', title: 'Text 07'}));
	// info_container.appendChild(newinput({name: 'info04', title: 'Text 04'}));
	// info_container.appendChild(newinput({name: 'info08', title: 'Text 08'}));

	// container.appendChild(info_container);

	// container.appendChild(dicedropdown());


	// adding existing category
	jQuery('#nyhettypechecklist').on('change', function(e) {

		let text = $(e.target).parent().text().trim().replace(/ /g, '-');

		if (!e.target.checked) $("input[name='nyhet_sort_"+text+"']").parent().remove();
		else {
			let input = newinput({
				name: 'nyhet_sort_'+text, 
				title: 'Sortering '+text.replace(/-/g, ' '), 
				notData: true, 
				sort: text, 
				type: 'number',
				step: 0.01
			});

			// $("input[name='nyhet_sort']").parent().parent().append(input);
			$('.nyhet-sort-container').append(input);
		}
	});

	// reading name of new category for creating
	jQuery('#newnyhettype').on('input', function(e) { newtype = e.target.value; });

	// creating category
	jQuery('#nyhettype-add-submit').click(function(e) {
		let text = newtype.trim().replace(/ /g, '-');
		text = text.replace('ø', 'o');
		text = text.replace('æ', 'ae');
		text = text.replace('å', 'a');

		let input = newinput({name: 'nyhet_sort_'+text, title: 'Sortering '+text.replace(/-/g, ' '), notData: true, sort: text, type: 'number'});
		$('.nyhet-sort-container').append(input);
		// $("input[name='nyhet_sort']").parent().parent().append(input);
	});

})();