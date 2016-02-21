/**
 * Created by Станислав on 28.07.15.
 */

/** подгружаем данные в зависимости от выбранного типа вопроса */
$('#select-type').change(function(){
    choice = $('#select-type option:selected').val();
    token = $('.form').children().eq(0).val();
    $.ajax({
        cache: false,
        type: 'POST',
        url:   '/uir/public/get-type',
        beforeSend: function (xhr) {
            var token = $('meta[name="csrf_token"]').attr('content');

            if (token) {
                return xhr.setRequestHeader('X-CSRF-TOKEN', token);
            }
        },
        data: { choice: choice, token: 'token' },
        success: function(data){
            $('#type_question_add').html(data);
        }
    });
    return false;
});

/** Формирование списка тем, соответствующих выбранному разделу */
$('#type_question_add').on('change','#select-section', function(){
    choice = $('#select-section option:selected').val();
    token = $('.form').children().eq(0).val();
    $.ajax({
        cache: false,
        type: 'POST',
        url:   '/uir/public/get-theme',
        beforeSend: function (xhr) {
            var token = $('meta[name="csrf_token"]').attr('content');

            if (token) {
                return xhr.setRequestHeader('X-CSRF-TOKEN', token);
            }
        },
        data: { choice: choice, token: 'token' },
        success: function(data){
            $('#container').html(data);
        }
    });
    return false;
});

/** заполнение preview в зависимсти от типа вопроса */
$('#type_question_add').on('click', '#preview-btn', function(){
    $('#preview-text').text($('#textarea1').val());                                                                  //вписываем текст вопроса
    var i;
    var str = '';
    var type = $('#select-type').val();
    switch (type) {                                                                                                  //для каждого типа вопроса заполняем варианты
        case 'Выбор одного из списка':
            $('.textarea3').each(function(){
                $('#preview-container').append('<input type="radio" value="'+$(this).val()+'"> '+$(this).val()+'<br>');
            });
            break;
        case 'Выбор нескольких из списка':
            $('.textarea3').each(function(){
                $('#preview-container').append('<input type="checkbox" value="'+$(this).val()+'"> '+$(this).val()+'<br>');
            });
            break;
        case 'Текстовый вопрос':
            //$('#preview-container').append($('#general-text').text());
            $('#general-text').clone().appendTo('#preview-container');                                               //клонируем в превью весь текст
            for (i=1; i<word_number; i++){                                                                           //идем по всем пропущенным словам
                str = '<span><select>\
                       <option disabled selected>Вставьте пропущенное слово</option>';
                $("#card-body-"+i+" textarea").each(function(){                                                      //составляем строку с вариантами для каждого пропущенного слова
                    if ($(this).next().text() != 'Стоимость'){
                        str += '<option value="'+$(this).val()+'">'+$(this).val()+'</option>';
                    }
                });
                str +=  '</select></span>';
                $('#preview-container #text-part-'+$('#card-body-'+i).attr('class').substring(15)).html(str);        //вставляем строку вместо пропущенного слова
            }
            $('#preview-container #general-text').children().removeAttr('id');                                       //удаляем ненужные атрибуты
            $('#preview-container #general-text').children().removeAttr('style');
            $('#preview-container #general-text').children().removeAttr('class');
            $('#preview-container #general-text').removeAttr('class');
            $('#preview-container #general-text').removeAttr('id');
            break;
        case 'Да/Нет':
            var trig_show = true;
            $('.textarea3').each(function(){
                if(trig_show){
                    $('#preview-container').append('<table class="table table-striped" id="prw-table"><tbody><tr><td>#</td><td>Верно</td><td>Неверно</td></tr></tbody></table>');
                }
                $('#prw-table').append('<tr><td>'+$(this).val()+'</td><td><input type="checkbox"></td><td><input type="checkbox"></td>');
                trig_show = false;
            });
            break;
    }
});

/** Закрытие окна предпросмотра */
$('#type_question_add').on('click', '#close-btn', function(){
    $('#preview-container').empty();
});

/** действия при сабмите формы */
$('#type_question_add').on('click', '.submit-question', function(){
    if ($('#select-section').val() == '$nbsp'){                                                                     //если не выбрали раздел
        alert('Вы не выбрали раздел и тему!');
        return false;
    }
    if ($('#select-theme').val() == '$nbsp'){                                                                       //если не выбрали тему
        alert('Вы не выбрали тему!');
        return false;
    }
    if ($('#select-type').val() == 'Текстовый вопрос'){
        if (word_number == 1){                                                                                          //если не выделили ни одного слова
            alert('Вы не выделили ни одного слова!');
            return false;
        }
        $('#number-of-blocks').val(word_number-1);                                                                      //заносим в форму информацию о количестве пропущенных слов
        var i;
        var sumCost = 0;
        var costStting = '';
        for (i=1; i<word_number; i++){
            $('#column-'+i+' textarea').first().val($('#column-'+i+' textarea').first().val().replace(/,/g, '.'));  //меняем в стоимости запятую на точку для правильной обработки на сервере
            sumCost += Number($('#column-'+i+' textarea').first().val());                                           //считаем сумму всех стоимостей
            costStting += $('#column-'+i+' textarea').eq(1).val()+': '+$('#column-'+i+' textarea').eq(0).val()+'\n';//создаем строку вида "слово: стоимость"
        }
        if (sumCost.toFixed(2) != '1.00'){                                                                          //не сабмитим, если стоимости не равны 1 в сумме
            alert('Сумма стоимостей должна быть равна единице!\n' + costStting);
            return false;
        }
        for (i=1; i<word_number; i++){
            $('#text-part-'+$('#card-body-'+i).attr('class').substring(15)).text($('#text-part-'+$('#card-body-'+i).attr('class').substring(15)).text().replace($('#text-part-'+$('#card-body-'+i).attr('class').substring(15)).text(), $('#text-part-'+$('#card-body-'+i).attr('class').substring(15)).text()+'|'+i));     //ко всем выделенным словам добавляем маркер вида |x, где x - номер пропущенного слова
            $('#edit-text').val($('#general-text').text());                                                         //записываем измененный текст в поле формы с текстом для отправки на сервер
            $('#column-'+i+' textarea').eq(1).val($('#column-'+i+' textarea').eq(1).val()+'|'+i);                   //верный вариант ответа также заменяем на него же с маркером
        }
    }
});
