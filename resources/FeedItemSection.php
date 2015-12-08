<?php
/**
 * FeedItemSection.php
 * @author Revin Roman
 * @link https://rmrevin.com
 */

namespace cookyii\modules\Feed\resources;

/**
 * Class FeedItemSection
 * @package cookyii\modules\Feed\resources
 *
 * @property integer $item_id
 * @property integer $section_id
 */
class FeedItemSection extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            /** type validators */
            [['item_id', 'section_id'], 'integer'],

            /** semantic validators */
            [['item_id', 'section_id'], 'required'],

            /** default values */
        ];
    }

    /**
     * @return \cookyii\modules\Feed\resources\queries\FeedItemSectionQuery
     */
    public static function find()
    {
        return \Yii::createObject(
            \cookyii\modules\Feed\resources\queries\FeedItemSectionQuery::className(),
            [get_called_class()]
        );
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%feed_item_section}}';
    }
}