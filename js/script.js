var jspAPI;

$(document).ready(function(){
	jspAPI = $('#chatLineHolder').jScrollPane({
		verticalDragMinHeight: 12,
		verticalDragMaxHeight: 12
	}).data('jsp');

	// Ловим отправку формы
	$('form').submit(function(e){
		e.preventDefault();

		var form = $(this), er = false;

		// Проверяем, чтобы не было пустых инаутов
		form.find('input').each(function(i,e) {
			if (empty($(this).val())) {
				er = true;
			}
		});

		// Если что-то пустое, не отправляем
		if (er) return false;

		var data = form.serialize();

		$.ajax({
			type: 'POST',
			url: 'php/send.php',
			data: data,
			dataType: 'json',
			success: function(msg){
				// Сообщение успешно отправлено
			},
			error: function(a,b,c) {
				console.log('Произошла ошибка');
			}
		});

		return false;
	});

	// Инициируем функцию, которая будет ждать сообщения
	get();
});

function get() {
	$.ajax({
		type: 'GET',
		url: 'php/get.php',
		dataType: 'json',
		success: function(msg) {
			// Если в ответ что-то пришло
			if (typeof msg !== 'undefined')
			{
				// И оно не пустое
				if (!empty(msg))
				{
					var d = new Date(), time, arr, message;

					// Задаем время
					time = (d.getHours() < 10 ? '0' : '' ) + d.getHours()+':'+
						(d.getMinutes() < 10 ? '0':'') + d.getMinutes();

					// Генерируем HTML-код для сообщения
					arr = [
						'<div class="chat rounded"><span class="author">',
						msg.name,
						':</span><span class="text">',
						msg.message,
						'</span><span class="time">',
						time,
						'</span></div>'
					];

					message = arr.join('');

					// Обновляем контейнер
					jspAPI.getContentPane().append(message);
					jspAPI.reinitialise();
					jspAPI.scrollToBottom(true);
				}
			}

			get();
		},
		error: function(a,b,c) {
			console.log('Произошла ошибка');
		}
	});
}

function empty(mixed_var) {
	'use strict';

	return ( mixed_var === '' || mixed_var === 0   || mixed_var === '0' || mixed_var === null  || mixed_var === undefined || mixed_var === false || mixed_var === '&nbsp;' || mixed_var === ' ');
}