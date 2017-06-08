<input type="hidden" id="count" value="4">
<div class="checkbox checkbox-styled">
    <label>
        <input type="checkbox" name="control" id="control">
        <span>Только для контрольных тестов</span>
    </label>
</div>
<div class="checkbox checkbox-styled">
    <label>
        <input type="checkbox" name="translated" id="translated">
        <span>Переведен на английский язык</span>
    </label>
</div>

<!-- Текст на русском языке -->
<div class="form-group">
    <textarea  name="title" id="textarea1" class="form-control" rows="3" placeholder="" required></textarea>
    <label for="textarea1">Текст</label>
</div>

<!-- Текст на английском языке -->
<div class="form-group">
    <textarea  name="eng-title" id="eng-textarea1" class="form-control" rows="3" placeholder=""></textarea>
    <label for="textarea1">Text</label>
</div>

<div id="text-images-container">
    <input type="file" name="text-images[]" id="text-image-input-1" class="text-image-input">
</div>
<br>

<!-- Русские варианты ответа -->
<div id="variants" class="col-md-6 col-sm-6">
    <div class="form-group col-md-6 col-sm-6">
        <textarea  name="variants[]"  class="form-control textarea3" rows="1" placeholder="" required></textarea>
        <label for="textarea3">Точка 1</label>
    </div>
    <div class="form-group col-md-6 col-sm-6">
        <textarea  name="answers[]"  class="form-control textarea3" rows="1" placeholder="" required></textarea>
        <label for="textarea3">Значение 1</label>
    </div>
    <div class="form-group col-md-6 col-sm-6">
        <textarea  name="variants[]"  class="form-control textarea3" rows="1" placeholder="" required></textarea>
        <label for="textarea3">Точка 2</label>
    </div>
    <div class="form-group col-md-6 col-sm-6">
        <textarea  name="answers[]"  class="form-control textarea3" rows="1" placeholder="" required></textarea>
        <label for="textarea3">Значение 2</label>
    </div>
    <div class="form-group col-md-6 col-sm-6">
        <textarea  name="variants[]"  class="form-control textarea3" rows="1" placeholder="" required></textarea>
        <label for="textarea3">Точка 3</label>
    </div>
    <div class="form-group col-md-6 col-sm-6">
        <textarea  name="answers[]"  class="form-control textarea3" rows="1" placeholder="" required></textarea>
        <label for="textarea3">Значение 3</label>
    </div>
</div>


<div id="other-options" class="col-md-10 col-sm-6">
    <div class="form-group">
        <select name="section" id="select-section" class="form-control" size="1" required>
            <option value="$nbsp"></option>
            @foreach ($sections as $section)
                <option value="{{$section['section_name']}}">{{$section['section_name']}}</option>/td>
            @endforeach
        </select>
        <label for="select-section">Раздел</label>
    </div>

    <div class="form-group" id="container">
        <!-- контейнер для ajax -->
    </div>

    <div class="form-group">
        <input type="number" min="1" name="points" id="points" class="form-control" value="1">
        <label for="points">Баллы за верный ответ</label>
    </div>

    <button class="btn btn-primary btn-raised submit-question" type="submit">Добавить вопрос</button>
    <a id="preview-btn" class="btn btn-primary btn-raised" href="#question-preview">Preview</a>
</div>
</div>  <!-- Закрываем card-body -->
</div>  <!-- Закрываем card -->
</div>  <!-- Закрываем col-md -->
</form>
<div id="question-preview" class="modalDialog">
    <div>
        <a id="close-btn" class="btn ink-reaction btn-floating-action btn-danger close" href="#close" title="Close">X</a>
        <h2>Предварительный просмотр</h2>
        <form class="smart-blue">
            <h1>Вопрос 1</h1>
            <h2 id="preview-text"></h2>
            <div id="preview-container"></div>
        </form>
        <button class="btn btn-primary btn-raised submit-question" type="submit">Добавить вопрос</button>
    </div>
</div>
</div>

{!! HTML::script('js/question_create/oneChoice.js') !!}
{!! HTML::script('js/question_create/imageInTitle.js') !!}