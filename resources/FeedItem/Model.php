<?php
/**
 * Model.php
 * @author Revin Roman
 * @link https://rmrevin.com
 */

namespace cookyii\modules\Feed\resources\FeedItem;

use cookyii\helpers\ApiAttribute;
use cookyii\modules\Feed\resources\FeedItemSection\Model as FeedItemSectionModel;
use cookyii\modules\Feed\resources\FeedSection\Model as FeedSectionModel;
use cookyii\modules\Media\resources\Media\Model as MediaModel;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class Model
 * @package cookyii\modules\Feed\resources\FeedItem
 *
 * @property integer $id
 * @property string $slug
 * @property string $title
 * @property integer $picture_media_id
 * @property string $content_preview
 * @property string $content_detail
 * @property string $meta
 * @property integer $sort
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $published_at
 * @property integer $archived_at
 * @property integer $deleted_at
 * @property integer $activated_at
 *
 * @property MediaModel $pictureMedia
 * @property FeedItemSectionModel[] $itemSections
 * @property FeedSectionModel[] $sections
 */
class Model extends \cookyii\db\ActiveRecord
{

    use \cookyii\db\traits\ActivationTrait,
        \cookyii\db\traits\SoftDeleteTrait;

    static $tableName = '{{%feed_item}}';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'blameable' => \cookyii\behaviors\BlameableBehavior::className(),
            'timestamp' => \cookyii\behaviors\TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();

        unset(
            $fields['meta'],
            $fields['created_by'], $fields['updated_by'],
            $fields['created_at'], $fields['updated_at'],
            $fields['published_at'], $fields['archived_at'],
            $fields['deleted_at'], $fields['activated_at']
        );

        $fields['published'] = [$this, 'isPublished'];
        $fields['archived'] = [$this, 'isArchived'];
        $fields['deleted'] = [$this, 'isDeleted'];
        $fields['activated'] = [$this, 'isActivated'];

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        $fields = parent::extraFields();

        $fields['meta'] = function (self $Model) {
            return $Model->meta();
        };

        $fields['picture_300'] = function (self $Model) {
            $result = null;

            $Media = $Model->pictureMedia;
            if (!empty($Media)) {
                $result = $Media->image()->resizeByWidth(300)->export();
            }

            return $result;
        };

        $fields['sections'] = function (self $Model) {
            $result = [];

            $item_sections = $Model->getItemSections()
                ->asArray()
                ->all();

            if (!empty($item_sections)) {
                $result = ArrayHelper::getColumn($item_sections, 'section_id');
                $result = array_map('intval', $result);
            }

            return $result;
        };

        ApiAttribute::datetimeFormat($fields, 'created_at');
        ApiAttribute::datetimeFormat($fields, 'updated_at');
        ApiAttribute::datetimeFormat($fields, 'published_at');
        ApiAttribute::datetimeFormat($fields, 'archived_at');
        ApiAttribute::datetimeFormat($fields, 'deleted_at');
        ApiAttribute::datetimeFormat($fields, 'activated_at');

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            /** type validators */
            [['slug', 'title', 'content_preview', 'content_detail', 'meta'], 'string'],
            [
                [
                    'picture_media_id', 'sort', 'created_by', 'updated_by',
                    'created_at', 'updated_at', 'published_at', 'archived_at', 'activated_at', 'deleted_at',
                ], 'integer',
            ],

            /** semantic validators */
            [['slug', 'title'], 'required'],
            [['slug'], 'unique', 'filter' => $this->isNewRecord ? null : ['not', ['id' => $this->id]]],
            [['slug', 'title', 'meta'], 'filter', 'filter' => 'str_clean'],
            [['content_preview', 'content_detail'], 'filter', 'filter' => 'str_pretty'],

            /** default values */
        ];
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return empty($this->published_at) || ($this->published_at <= time() && !$this->isArchived());
    }

    /**
     * @return bool
     */
    public function isArchived()
    {
        return !empty($this->archived_at) && $this->archived_at <= time();
    }

    /**
     * @param mixed $defaultValues
     * @return mixed
     */
    public function meta($defaultValues = [])
    {
        return empty($this->meta) || $this->meta === '[]'
            ? $defaultValues
            : Json::decode($this->meta);
    }

    /**
     * @return \cookyii\modules\Media\resources\Media\Query
     */
    public function getPictureMedia()
    {
        /** @var MediaModel $MediaModel */
        $MediaModel = \Yii::createObject(MediaModel::className());

        /** @var \cookyii\modules\Media\resources\Media\Query $Query */
        $Query = $this->hasOne($MediaModel::className(), ['id' => 'picture_media_id']);

        return $Query;
    }

    /**
     * @return \cookyii\modules\Feed\resources\FeedItemSection\Query
     */
    public function getItemSections()
    {
        /** @var FeedItemSectionModel $ItemSectionModel */
        $ItemSectionModel = \Yii::createObject(FeedItemSectionModel::className());

        /** @var \cookyii\modules\Feed\resources\FeedItemSection\Query $Query */
        $Query = $this->hasMany($ItemSectionModel::className(), ['item_id' => 'id']);

        return $Query
            ->inverseOf('item');
    }

    /**
     * @return \cookyii\modules\Feed\resources\FeedSection\Query
     */
    public function getSections()
    {
        /** @var FeedSectionModel $SectionModel */
        $SectionModel = \Yii::createObject(FeedSectionModel::className());

        /** @var \cookyii\modules\Feed\resources\FeedSection\Query $Query */
        $Query = $this->hasMany($SectionModel::className(), ['id' => 'section_id']);

        return $Query
            ->via('itemSections');
    }

    /**
     * @return Query
     */
    public static function find()
    {
        return \Yii::createObject(Query::class, [get_called_class()]);
    }
}
