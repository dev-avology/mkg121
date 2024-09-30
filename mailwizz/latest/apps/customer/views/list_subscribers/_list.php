<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

/** @var Controller $controller */
$controller = controller();

/** @var Lists $list */
$list = $controller->getData('list');

/** @var ListSubscriber $subscriber */
$subscriber = $controller->getData('subscriber');

/** @var array $columns */
$columns = (array)$controller->getData('columns');

/** @var array $rows */
$rows = (array)$controller->getData('rows');

/** @var CPagination $pages */
$pages = $controller->getData('pages');

/** @var int $count */
$count = (int)$controller->getData('count');

/**
 * This hook gives a chance to prepend content or to replace the default view content with a custom content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->getData()}
 * In case the content is replaced, make sure to set {@CAttributeCollection $collection->add('renderContent', false)}
 * in order to stop rendering the default content.
 * @since 1.3.3.1
 */
hooks()->doAction('before_view_file_content', $viewCollection = new CAttributeCollection([
    'controller'    => $controller,
    'renderContent' => true,
]));

// and render if allowed
if ($viewCollection->itemAt('renderContent')) { ?>
    <div class="table-responsive subscribers-table">
        <div class="pull-left bulk-selected-options" style="display:none; margin-bottom: 5px;">
            <?php echo CHtml::dropDownList('bulk_action', null, CMap::mergeArray(['' => t('list_subscribers', 'With selected:')], $subscriber->getBulkActionsList()), [
                'class'         => 'form-control bulk-action',
                'data-bulkurl'  => createUrl('list_subscribers/bulk_action', ['list_uid' => $list->list_uid]),
                'data-delete'   => t('app', 'Are you sure you want to delete this item? There is no way coming back after you do it.'),
            ]); ?>
        </div>
        <div class="pull-right">
            <span class="counter">
                <?php echo t('list_subscribers', 'Found {count} subscribers.', ['{count}' => formatter()->formatNumber($count)]); ?>
            </span>
        </div>
        <div class="clearfix"><!-- --></div>
        <?php echo CHtml::form('', 'post', ['id' => 'subscribers-form']); ?>
        <table class="table table-hover">
            <thead>
                <tr>
                    <?php foreach ($columns as $column) { ?>
                    <th><?php echo html_encode((string)$column['label']); ?></th>
                    <?php } ?>
                </tr>
                <tr>
                    <?php foreach ($columns as $index => $column) { ?>
                    <th>
                    <?php
                        $column['htmlOptions'] = isset($column['htmlOptions']) && is_array($column['htmlOptions']) ? $column['htmlOptions'] : [];
                        if ($column['field_type'] == 'text') {
                            echo CHtml::textField('filter[' . $column['field_id'] . ']', $column['value']);
                        } elseif ($column['field_type'] == 'select') {
                            echo CHtml::dropDownList('filter[' . $column['field_id'] . ']', $column['value'], $column['options']);
                        } elseif ($column['field_type'] == 'checkbox') {
                            echo CHtml::checkbox('filter[' . $column['field_id'] . ']', $column['checked'], $column['htmlOptions']);
                        } else {
                            echo CHtml::tag('div', $column['htmlOptions']);
                        }
                    ?>
                    </th>
                    <?php } ?>
                </tr>
            </thead>
            <?php if (count($rows) > 0) { ?>
            <tbody>
                <?php foreach ($rows as $row) { ?>
                <tr>
                    <?php foreach ($row['columns'] as $column) { ?>
                    <td><?php echo (string)$column; ?></td>
                    <?php } ?>    
                </tr>
                <?php } ?>
            </tbody>
            <?php } else { ?>
            <tbody>
                <tr>
                    <td colspan="<?php echo count($columns); ?>" align="center">
                        <?php echo t('list_subscribers', 'Sorry, but there are no subscribers to show right now.'); ?>
                    </td>
                </tr>
            </tbody>
            <?php } ?>
        </table>
        <?php echo CHtml::submitButton('submit', ['name' => 'do', 'style' => 'display:none', 'id' => 'submit-subscribe-form']); ?>
        <?php echo CHtml::endForm(); ?>
    </div>    
    <div class="clearfix" style="height: 10px;"><!-- --></div>
    <div class="row-fluid">
        <div class="pull-right">
		    <?php echo $subscriber->paginationOptions->getGridFooterPagination([
                'onchange'              => '',
                'id'                    => 'subscribers-grid-page-size',
                'data-page-size-var'    => $subscriber->paginationOptions->pageSizeVar,
            ]); ?>
        </div>
        <div class="clearfix">&nbsp;</div>
        <hr />
        <div class="pull-right">
            <?php $controller->widget('CLinkPager', [
                'pages'         => $pages,
                'htmlOptions'   => ['id' => 'subscribers-pagination', 'class' => 'pagination'],
                'header'        => false,
                'cssFile'       => false,
            ]); ?>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
<?php
}
/**
 * This hook gives a chance to append content after the view file default content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->getData()}
 * @since 1.3.3.1
 */
hooks()->doAction('after_view_file_content', new CAttributeCollection([
    'controller'        => $controller,
    'renderedContent'   => $viewCollection->itemAt('renderContent'),
]));
