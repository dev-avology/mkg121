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
 * @since 1.3.3.1
 */

/** @var Controller $controller */
$controller = controller();

/** @var string $pageHeading */
$pageHeading = (string)$controller->getData('pageHeading');

/** @var FeedbackLoopServer $server */
$server = $controller->getData('server');

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
if ($viewCollection->itemAt('renderContent')) {
    /**
     * @since 1.3.9.2
     */
    $itemsCount = (int)FeedbackLoopServer::model()->countByAttributes([
        'customer_id' => (int)customer()->getId(),
        'status'      => array_keys($server->getStatusesList()),
    ]); ?>
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
                    ->add('<h3 class="box-title">' . IconHelper::make('glyphicon-transfer') . html_encode((string)$pageHeading) . '</h3>')
                    ->render(); ?>
            </div>
            <div class="pull-right">
                <?php BoxHeaderContent::make(BoxHeaderContent::RIGHT)
                    ->addIf($controller->widget('common.components.web.widgets.GridViewToggleColumns', ['model' => $server, 'columns' => ['name', 'hostname', 'username', 'service', 'protocol', 'status', 'port']], true), $itemsCount)
                    ->add(CHtml::link(IconHelper::make('create') . t('app', 'Create new'), ['feedback_loop_servers/create'], ['class' => 'btn btn-primary btn-flat', 'title' => t('app', 'Create new')]))
                    ->addIf(CHtml::link(IconHelper::make('export') . t('app', 'Export'), ['feedback_loop_servers/export'], ['target' => '_blank', 'class' => 'btn btn-primary btn-flat', 'title' => t('app', 'Export')]), $itemsCount)
                    ->add(CHtml::link(IconHelper::make('refresh') . t('app', 'Refresh'), ['feedback_loop_servers/index'], ['class' => 'btn btn-primary btn-flat', 'title' => t('app', 'Refresh')]))
                    ->add(CHtml::link(IconHelper::make('info'), '#page-info', ['class' => 'btn btn-primary btn-flat', 'title' => t('app', 'Info'), 'data-toggle' => 'modal']))
                    ->render(); ?>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body">
            <div class="table-responsive">
            <?php
            /**
             * This hook gives a chance to prepend content or to replace the default grid view content with a custom content.
             * Please note that from inside the action callback you can access all the controller view
             * variables via {@CAttributeCollection $collection->controller->getData()}
             * In case the content is replaced, make sure to set {@CAttributeCollection $collection->itemAt('renderGrid')} to false
             * in order to stop rendering the default content.
             * @since 1.3.3.1
             */
            hooks()->doAction('before_grid_view', $collection = new CAttributeCollection([
                'controller'    => $controller,
                'renderGrid'    => true,
            ]));

    /**
     * This widget renders default getting started page for this particular section.
     * @since 1.3.9.2
     */
    $controller->widget('common.components.web.widgets.StartPagesWidget', [
                'collection' => $collection,
                'enabled'    => !$itemsCount,
            ]);

    // and render if allowed
    if ($collection->itemAt('renderGrid')) {
        $controller->widget('zii.widgets.grid.CGridView', hooks()->applyFilters('grid_view_properties', [
                    'ajaxUrl'           => createUrl($controller->getRoute()),
                    'id'                => $server->getModelName() . '-grid',
                    'dataProvider'      => $server->search(),
                    'filter'            => $server,
                    'filterPosition'    => 'body',
                    'filterCssClass'    => 'grid-filter-cell',
                    'itemsCssClass'     => 'table table-hover',
                    'selectableRows'    => 0,
                    'enableSorting'     => false,
                    'cssFile'           => false,
                    'pagerCssClass'     => 'pagination pull-right',
                    'pager'             => [
                        'class'         => 'CLinkPager',
                        'cssFile'       => false,
                        'header'        => false,
                        'htmlOptions'   => ['class' => 'pagination'],
                    ],
                    'columns' => hooks()->applyFilters('grid_view_columns', [
                        [
                            'name'  => 'name',
                            'value' => 'empty($data->name) ? null : CHtml::link($data->hostname, createUrl("feedback_loop_servers/update", array("id" => $data->server_id)))',
                            'type'  => 'raw',
                        ],
                        [
                            'name'  => 'hostname',
                            'value' => 'CHtml::link($data->hostname, createUrl("feedback_loop_servers/update", array("id" => $data->server_id)))',
                            'type'  => 'raw',
                        ],
                        [
                            'name'  => 'username',
                            'value' => '$data->username',
                        ],
                        [
                            'name'  => 'service',
                            'value' => '$data->serviceName',
                            'filter'=> $server->getServicesArray(),
                        ],
                        [
                            'name'  => 'protocol',
                            'value' => '$data->protocolName',
                            'filter'=> $server->getProtocolsArray(),
                        ],
                        [
                            'name'  => 'status',
                            'value' => 'ucfirst(t("app", $data->status))',
                            'filter'=> $server->getStatusesList(),
                        ],
                        [
                            'name'  => 'port',
                            'value' => '$data->port',
                        ],
                        [
                            'class'     => 'DropDownButtonColumn',
                            'header'    => t('app', 'Options'),
                            'footer'    => $server->paginationOptions->getGridFooterPagination(),
                            'buttons'   => [
                                'update' => [
                                    'label'     => IconHelper::make('update'),
                                    'url'       => 'createUrl("feedback_loop_servers/update", array("id" => $data->server_id))',
                                    'imageUrl'  => null,
                                    'options'   => ['title' => t('app', 'Update'), 'class' => 'btn btn-primary btn-flat'],
                                ],
                                'copy'=> [
                                    'label'     => IconHelper::make('copy'),
                                    'url'       => 'createUrl("feedback_loop_servers/copy", array("id" => $data->server_id))',
                                    'imageUrl'  => null,
                                    'options'   => ['title' => t('app', 'Copy'), 'class' => 'btn btn-primary btn-flat copy-server'],
                                ],
                                'enable'=> [
                                    'label'     => IconHelper::make('glyphicon-open'),
                                    'url'       => 'createUrl("feedback_loop_servers/enable", array("id" => $data->server_id))',
                                    'imageUrl'  => null,
                                    'options'   => ['title' => t('app', 'Enable'), 'class' => 'btn btn-primary btn-flat enable-server'],
                                    'visible'   => '$data->getIsDisabled()',
                                ],
                                'disable'=> [
                                    'label'     => IconHelper::make('save'),
                                    'url'       => 'createUrl("feedback_loop_servers/disable", array("id" => $data->server_id))',
                                    'imageUrl'  => null,
                                    'options'   => ['title' => t('app', 'Disable'), 'class' => 'btn btn-primary btn-flat disable-server'],
                                    'visible'   => '$data->getIsActive()',
                                ],
                                'delete' => [
                                    'label'     => IconHelper::make('delete'),
                                    'url'       => 'createUrl("feedback_loop_servers/delete", array("id" => $data->server_id))',
                                    'imageUrl'  => null,
                                    'options'   => ['title' => t('app', 'Delete'), 'class' => 'btn btn-danger btn-flat delete'],
                                ],
                            ],
                            'headerHtmlOptions' => ['style' => 'text-align: right'],
                            'footerHtmlOptions' => ['align' => 'right'],
                            'htmlOptions'       => ['align' => 'right', 'class' => 'options'],
                            'template'          => '{update} {copy} {enable} {disable} {delete}',
                        ],
                    ], $controller),
                ], $controller));
    }
    /**
     * This hook gives a chance to append content after the grid view content.
     * Please note that from inside the action callback you can access all the controller view
     * variables via {@CAttributeCollection $collection->controller->getData()}
     * @since 1.3.3.1
     */
    hooks()->doAction('after_grid_view', new CAttributeCollection([
                'controller'    => $controller,
                'renderedGrid'  => $collection->itemAt('renderGrid'),
            ])); ?>
            <div class="clearfix"><!-- --></div>
            </div>
        </div>
    </div>
    <!-- modals -->
    <div class="modal modal-info fade" id="page-info" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><?php echo IconHelper::make('info') . t('app', 'Info'); ?></h4>
                </div>
                <div class="modal-body">
                    <?php
                    $text = 'Please note, when adding a feedback loop server make sure the email address is used only for reading feedback email but nothing more.<br />
                    This is important since the script that checks the bounced emails needs to read all the emails from the account you specify and beside it can be time and memory consuming, it will also delete all the emails from the email account.';
    echo t('servers', StringHelper::normalizeTranslationString($text)); ?>
                </div>
            </div>
        </div>
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
