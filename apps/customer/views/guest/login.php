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

/** @var CustomerLogin $model */
$model = $controller->getData('model');

/** @var bool $registrationEnabled */
$registrationEnabled = (bool)$controller->getData('registrationEnabled');

/** @var bool $facebookEnabled */
$facebookEnabled = (bool)$controller->getData('facebookEnabled');

/** @var bool $twitterEnabled */
$twitterEnabled = (bool)$controller->getData('twitterEnabled');

/** @var string $loginBgImage */
$loginBgImage = (string)$controller->getData('loginBgImage');

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
     * This hook gives a chance to prepend content before the active form or to replace the default active form entirely.
     * Please note that from inside the action callback you can access all the controller view variables
     * via {@CAttributeCollection $collection->controller->getData()}
     * In case the form is replaced, make sure to set {@CAttributeCollection $collection->add('renderForm', false)}
     * in order to stop rendering the default content.
     * @since 1.3.3.1
     */
    hooks()->doAction('before_active_form', $collection = new CAttributeCollection([
        'controller'    => $controller,
        'renderForm'    => true,
    ]));

    // and render if allowed
    if ($collection->itemAt('renderForm')) {
        /** @var CActiveForm $form */
        $form = $controller->beginWidget('CActiveForm'); ?>
        <div class="login-flex" >
            <div class="login-form login-flex-col" style="display:none">
                <div class="login-box-body">
                    <p class="login-box-msg"><?php echo t('users', 'Sign in to start your session'); ?></p>
                    <?php
                    /**
                     * This hook gives a chance to prepend content before the active form fields.
                     * Please note that from inside the action callback you can access all the controller view variables
                     * via {@CAttributeCollection $collection->controller->getData()}
                     * @since 1.3.3.1
                     */
                    hooks()->doAction('before_active_form_fields', new CAttributeCollection([
                        'controller'    => $controller,
                        'form'          => $form,
                    ])); ?>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <?php echo $form->labelEx($model, 'email'); ?>
                                <?php echo $form->emailField($model, 'email', $model->fieldDecorator->getHtmlOptions('email')); ?>
                                <?php echo $form->error($model, 'email'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <?php echo $form->labelEx($model, 'password'); ?>
                                <?php echo $form->passwordField($model, 'password', $model->fieldDecorator->getHtmlOptions('password')); ?>
                                <?php echo $form->error($model, 'password'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>
                                    <?php echo $form->checkBox($model, 'remember_me') . ' ' . $model->getAttributeLabel('remember_me'); ?>
                                </label>
                            </div>
                            <div class="clearfix"><!-- --></div>
                            <div class="pull-left">
                                <a href="<?php echo createUrl('guest/forgot_password'); ?>" class="btn btn-default btn-flat"><?php echo IconHelper::make('fa-lock') . '&nbsp;' . t('customers', 'Forgot password?'); ?></a>
                                <?php if ($registrationEnabled) { ?>
                                    <a href="<?php echo createUrl('guest/register'); ?>" class="btn btn-default btn-flat"><?php echo IconHelper::make('fa-user') . '&nbsp;' . t('customers', 'Register'); ?></a>
                                <?php } ?>
                            </div>
                            <div class="pull-right">
                                <button type="submit" class="btn btn-primary btn-flat"><?php echo IconHelper::make('next') . '&nbsp;' . t('app', 'Login'); ?></button>
                            </div>
                            <div class="clearfix"><!-- --></div>
                            <?php if (!empty($facebookEnabled) || !empty($twitterEnabled)) { ?>
                                <hr />
                                <div class="pull-left">
                                    <?php if (!empty($facebookEnabled)) { ?>
                                        <a href="<?php echo createUrl('guest/facebook'); ?>" class="btn btn-success btn-flat btn-facebook"><i class="fa fa-facebook-square"></i> <?php echo t('app', 'Login with Facebook'); ?></a>
                                    <?php } ?>
                                    <?php if (!empty($twitterEnabled)) { ?>
                                        <a href="<?php echo createUrl('guest/twitter'); ?>" class="btn btn-success btn-flat btn-twitter"><i class="fa fa-twitter-square"></i> <?php echo t('app', 'Login with Twitter'); ?></a>
                                    <?php } ?>
                                </div>
                                <div class="clearfix"><!-- --></div>
                            <?php } ?>
                        </div>
                    </div>

                    <?php
                    /**
                     * This hook gives a chance to append content after the active form fields.
                     * Please note that from inside the action callback you can access all the controller view variables
                     * via {@CAttributeCollection $collection->controller->getData()}
                     *
                     * @since 1.3.3.1
                     */
                    hooks()->doAction('after_active_form_fields', new CAttributeCollection([
                        'controller'    => $controller,
                        'form'          => $form,
                    ])); ?>
                </div>
            </div>
            <!--div class="login-billboard login-flex-col" style="background-image: url('<?php echo html_encode((string)$loginBgImage); ?>');width:100%" style="display:none">

            </div-->
        </div>
        <?php
        $controller->endWidget();
    } ?>
	 <div class="login-flex-home">
		 <div class="row">
			<div class="text-center">
			  <img src="https://staging3.booostr.co/wp-content/themes/pointfinder-child-theme/assets/images/logo-home.png" class="rounded mt-10 logo" alt="...">
			  
			  <h3 class="text-white header-title">Email Marketing System</h3>
				<p  class="text-white text-description">To take full advantage of Boostr's marketing and communication tools for your booster club you will need to :</p>
			  <a href="https://booostr.co/login/" target="_blank" class="btn btn-secondary btn-lg home-btn">Login or create a user acoount at boostr.co</a>
			</div>
			<div class="text-center banner-image">    
			  <div class="row">
				<div class="col-sm-12">
				  <img src="https://staging3.booostr.co/wp-content/themes/pointfinder-child-theme/assets/images/technology.png" class="img-responsive" alt="Image">
				</div>
				
			  </div>
			</div>
		</div>
	</div>
	<?php
	
    /**
     * This hook gives a chance to append content after the active form.
     * Please note that from inside the action callback you can access all the controller view variables
     * via {@CAttributeCollection $collection->controller->getData()}
     * @since 1.3.3.1
     */
    hooks()->doAction('after_active_form', new CAttributeCollection([
        'controller'      => $controller,
        'renderedForm'    => $collection->itemAt('renderForm'),
    ]));
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
