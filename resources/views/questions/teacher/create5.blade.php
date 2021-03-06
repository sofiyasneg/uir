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

    <!--  Русская таблица соответствия-->
    <div>
        <button class="btn btn-primary btn-raised" type="button" id="build-table">Построить таблицу</button>
        <input type="number" id="table-tr" min="1" max="10" required>
        <span> на </span>
        <input type="number" id="table-td" min="1" max="10" required>
        <span> элементов. </span>
        <br>
        <br>
        <div id="table-place">
            <!-- container for table -->
        </div>
    </div>

    <!--  Английская таблица соответствия-->
    <div>
        <button class="btn btn-primary btn-raised" type="button" id="build-table-eng">Build table</button>
        <input type="number" id="table-tr-eng" min="1" max="10" required>
        <span> X </span>
        <input type="number" id="table-td-eng" min="1" max="10" required>
        <span> items. </span>
        <br>
        <br>
        <div id="table-place-eng">
            <!-- container for english table -->
        </div>
    </div>

    <div id="other-options" class="col-md-10 col-sm-6">
        <div class="form-group">
            <select name="section" id="select-section" class="form-control" size="1">
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
    </div>
</div>
</div>
</div>
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


{!! HTML::script('js/question_create/accordanceTable.js') !!}
{!! HTML::script('js/question_create/accordanceTableEng.js') !!}