(() => {

	// console.log(emnyhet_meta);

	let newtype = '';

	// meta box
	let container = document.querySelector('.emnyhet-meta-container');

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

		let container = newdiv({class: 'emnyhet-input-container'});

		let title = newdiv({class: 'emnyhet-input-title', text: o.title});
		container.appendChild(title);

		let input = document.createElement('input');

		if (!o.type) input.setAttribute('type', 'text');
		else input.setAttribute('type', o.type);

		if (!o.sort) input.setAttribute('value', (emnyhet_meta.meta[o.name] == undefined) ? '' : emnyhet_meta.meta[o.name]);
		else {
			let sort = emnyhet_meta.emnyhet_sort;

			if (o.sort != 'default') sort = emnyhet_meta['emnyhet_sort_'+o.sort];

			if (sort == undefined) sort = emnyhet_meta.emnyhet_sort;

			input.setAttribute('value', sort);
		}

		if (o.step) input.setAttribute('step', parseFloat(o.step));
		if (o.max) input.setAttribute('max', parseFloat(o.step));
		if (o.min) input.setAttribute('min', parseFloat(o.step));



		if (!o.notData) input.setAttribute('name', 'emnyhet_data['+o.name+']');
		else input.setAttribute('name', o.name);

		container.appendChild(input);


		return container;
	}

	// creating the drop down for dice selection
	let dicedropdown = (o = {}) => {
		let container = document.createElement('div');

		let input = document.createElement('select');
		input.setAttribute('name', 'emnyhet_data[terning]');

		container.appendChild(newdiv({class: 'emnyhet-input-title', text: 'Terningkast'}));

		// helper function for creating option tag
		let addOption = (o = {}) => {
			let option = document.createElement('option');
			option.setAttribute('value', o.value);
			if (o.value == emnyhet_meta.meta.terning) option.setAttribute('selected', '');
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

	let container_sort = newdiv({class: 'emnyhet-sort-container'});
	container_sort.appendChild(newinput({
		name: 'emnyhet_sort', 
		title: 'Sortering', 
		notData: true, 
		sort: 'default', 
		type: 'number',
		step: 0.01
	}));

	container.appendChild(container_sort);

	for (let sort of emnyhet_meta['tax'])
		container_sort.appendChild(newinput({
			name: 'emnyhet_sort_'+sort, 
			title: 'Sortering '+sort.replace(/-/g, ' '), 
			notData: true, 
			sort: sort, 
			type: 'number',
			step: 0.01
		}));

	// container.appendChild(newinput({name: 'readmore', title: 'Read More Link'}));

	container.appendChild(newinput({name: 'list_title', title: 'Nyhet title i liste'}));
	container.appendChild(newinput({name: 'list_text', title: 'Nyhet tekst i liste'}));

	// let info_container = newdiv({class: 'emnyhet-info-container'});

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
	jQuery('#emnyhettypechecklist').on('change', function(e) {

		let text = $(e.target).parent().text().trim().replace(/ /g, '-');

		if (!e.target.checked) $("input[name='emnyhet_sort_"+text+"']").parent().remove();
		else {
			let input = newinput({
				name: 'emnyhet_sort_'+text, 
				title: 'Sortering '+text.replace(/-/g, ' '), 
				notData: true, 
				sort: text, 
				type: 'number',
				step: 0.01
			});

			// $("input[name='emnyhet_sort']").parent().parent().append(input);
			$('.emnyhet-sort-container').append(input);
		}
	});

	// reading name of new category for creating
	jQuery('#newemnyhettype').on('input', function(e) { newtype = e.target.value; });

	// creating category
	jQuery('#emnyhettype-add-submit').click(function(e) {
		let text = newtype.trim().replace(/ /g, '-');
		let input = newinput({name: 'emnyhet_sort_'+text, title: 'Sortering '+text.replace(/-/g, ' '), notData: true, sort: text, type: 'number'});
		$('.emnyhet-sort-container').append(input);
		// $("input[name='emnyhet_sort']").parent().parent().append(input);
	});

})();