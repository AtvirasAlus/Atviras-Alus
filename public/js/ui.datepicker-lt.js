jQuery(function($){
	$.datepicker.regional['lt'] = {
		clearText: 'Išvalyti', 
		clearStatus: '',
		closeText: 'Uždaryti', 
		closeStatus: 'Atmesti pokyčius ir uždaryti',
		prevText: '<Ankstesnis', 
		prevStatus: 'Rodyti ankstesnį mėnesį',
		nextText: 'Kitas>', 
		nextStatus: 'Rodyti kitą mėnesį',
		currentText: 'Šiandiena', 
		currentStatus: 'Rodyti dabartinį mėnesį',
		monthNames: ['Sausis','Vasaris','Kovas','Balandis','Gegužė','Birželis',
		'Liepa','Rugpjūtis','Rugsėjis','Spalis','Lapkritis','Gruodis'],
		monthNamesShort: ['Sau','Vas','Kov','Bal','Geg','Bir',
		'Lie','Rgp','Rgs','Spa','Lap','Grd'],
		monthStatus: 'Rodyti kitą mėnesį', 
		yearStatus: 'Rodyti kitus metus',
		weekHeader: 'Sv', 
		weekStatus: '',
		dayNames: ['Sekmadienis','Pirmadienis','Antradienis','Trečiadienis','Ketvirtadienis','Penktadienis','Šeštadienis'],
		dayNamesShort: ['Sek','Pir','Ant','Tre','Ket','Pen','Šeš'],
		dayNamesMin: ['Se','Pi','An','Tr','Ke','Pe','Še'],
		dayStatus: 'Nustatyti DD pirma savaitės diena', 
		dateStatus: 'Pasirinkite DD, MM d',
		dateFormat: 'yy-mm-dd', 
		firstDay: 1, 
		initStatus: 'Pasirinkite datą', 
		isRTL: false
	};
	$.datepicker.setDefaults($.datepicker.regional['lt']);
});