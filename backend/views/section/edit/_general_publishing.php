<?php
/**
 * _general_publishing.php
 * @author Revin Roman
 * @link https://rmrevin.com
 *
 * @var yii\web\View $this
 * @var cookyii\widgets\angular\ActiveForm $ActiveForm
 * @var cookyii\modules\Feed\backend\forms\SectionEditForm $SectionEditForm
 */

echo $ActiveForm->field($SectionEditForm, 'published_at')
    ->textInput([
        'ng-datetime-picker' => true,
        'placeholder' => Formatter()->asDatetime(time(), 'dd.MM.yyyy HH:mm'),
    ]);

echo $ActiveForm->field($SectionEditForm, 'archived_at')
    ->textInput([
        'ng-date-picker' => true,
        'ng-date-start' => 'data.published_at',
        'placeholder' => Formatter()->asDate(time() + (86400 * 180), 'dd.MM.yyyy'),
    ]);