<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * ListSubscribersGrowthWidget
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.0.34
 */

class ListSubscribersGrowthWidget extends CWidget
{
    /**
     * @var Lists|null
     */
    public $list;

    /**
     * @return void
     * @throws CException
     */
    public function run()
    {
        $list = $this->list;
        if (empty($list)) {
            return;
        }

        $criteria = new CDbCriteria();
        $criteria->compare('list_id', $list->list_id);

        /** @var ListSubscriberCountHistory|null $found */
        $found = ListSubscriberCountHistory::model()->find($criteria);
        if (empty($found)) {
            return;
        }

        $dateRanges = $this->getDateRangesList();

        clientScript()->registerScriptFile('https://cdn.jsdelivr.net/npm/chart.js@^3');
        clientScript()->registerScriptFile('https://cdn.jsdelivr.net/npm/moment@^2');
        clientScript()->registerScriptFile('https://cdn.jsdelivr.net/npm/chartjs-adapter-moment');
        clientScript()->registerScriptFile(apps()->getBaseUrl('assets/js/list-growth.js'));

        $this->render('list-growth', compact('list', 'dateRanges'));
    }

    /**
     * @return array
     */
    protected function getDateRangesList(): array
    {
        $ranges = [
            '12 hours' => '12 Hours',
            '1 day'    => '1 Day',
            '1 week'   => '1 Week',
            '1 month'  => '1 Month',
            '3 months' => '3 Months',
            '6 months' => '6 Months',
            '1 year'   => '1 Year',
        ];

        $list       = [];
        $dateFormat = 'Y-m-d H:i:s';
        $dateEnd    = Carbon\Carbon::now();
        foreach ($ranges as $interval => $name) {
            $dateStart = \Carbon\Carbon::createFromTimestamp((int)strtotime(sprintf('-%s', $interval)));
            $list[sprintf('%s - %s', $dateStart->format($dateFormat), $dateEnd->format($dateFormat))] = $name;
        }

        return $list;
    }
}
