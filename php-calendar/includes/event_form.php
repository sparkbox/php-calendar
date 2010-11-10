<?php
/*
 * Copyright 2010 Sean Proctor
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

if(!defined('IN_PHPC')) {
       die("Hacking attempt");
}

require_once("$phpc_includes_path/form.php");

function event_form() {
	global $phpc_script, $year, $month, $day, $vars, $phpcdb, $phpcid;

	$hour24 = get_config($phpcid, 'hours_24');
        $form = new Form($phpc_script, _('Event Form'));
        $form->add_part(new FormFreeQuestion('subject', _('Subject'),
				false, 32, true));
        $form->add_part(new FormLongFreeQuestion('description',
                                _('Description')));

	$when_group = new FormGroup(_('When'));
	$when_group->add_part(new FormDateTimeQuestion('start',
				_('From'), $hour24));
	$when_group->add_part(new FormDateTimeQuestion('end', _('To'),
				$hour24));

	$time_type = new FormDropDownQuestion('time-type', _('Time Type'));
	$time_type->add_option('normal', _('Normal'));
	$time_type->add_option('full', _('Full Day'));
	$time_type->add_option('tba', _('To Be Announced'));
	$time_type->add_option('none', _('None'));

	$when_group->add_part($time_type);

	$form->add_part($when_group);

        $repeat_type = new FormDropdownQuestion('repeats', _('Repeats'),
			array(), true, 'never');
        $repeat_type->add_option('never', _('Never'));
	$daily_group = new FormGroup();
        $repeat_type->add_option('daily', _('Daily'), NULL, $daily_group);
	$weekly_group = new FormGroup();
        $repeat_type->add_option('weekly', _('Weekly'), NULL, $weekly_group);
	$monthly_group = new FormGroup();
        $repeat_type->add_option('monthly', _('Monthly'), NULL, $monthly_group);
	$yearly_group = new FormGroup();
        $repeat_type->add_option('yearly', _('Yearly'), NULL, $yearly_group);

	$every_day = new FormDropdownQuestion('every-day', _('Every'),
			_('Repeat every how many days?'));
	$every_day->add_options(create_sequence(1, 30));
	$daily_group->add_part($every_day);
	$daily_group->add_part(new FormDateQuestion('daily-until', _('Until')));

	$every_week = new FormDropdownQuestion('every-week', _('Every'),
			_('Repeat every how many weeks?'));
	$every_week->add_options(create_sequence(1, 30));
	$weekly_group->add_part($every_week);
	$weekly_group->add_part(new FormDateQuestion('weekly-until',
				_('Until')));

	$every_month = new FormDropdownQuestion('every-month', _('Every'),
			_('Repeat every how many months?'));
	$every_month->add_options(create_sequence(1, 30));
	$monthly_group->add_part($every_month);
	$monthly_group->add_part(new FormDateQuestion('monthly-until',
				_('Until')));

	$every_year = new FormDropdownQuestion('every-year', _('Every'),
			_('Repeat every how many years?'));
	$every_year->add_options(create_sequence(1, 30));
	$yearly_group->add_part($every_year);
	$yearly_group->add_part(new FormDateQuestion('yearly-until',
				_('Until')));

        $when_group->add_part($repeat_type);

	if(can_create_readonly($phpcid))
		$form->add_part(new FormCheckBoxQuestion('readonly',
					_('Read-only')));

	$categories = new FormDropdownQuestion('catid', _('Category'));
	$categories->add_option('', _('None'));
	$have_categories = false;
	foreach($phpcdb->get_categories($phpcid) as $category) {
		$categories->add_option($category['catid'], $category['name']);
		$have_categories = true;
	}
	if($have_categories)
		$form->add_part($categories);

	if(isset($vars['phpcid']))
		$form->add_hidden('phpcid', $vars['phpcid']);

	$form->add_hidden('phpc_token', $_SESSION['phpc_token']);

	$form->add_hidden('action', 'event_submit');
	$form->add_part(new FormSubmitButton(_("Submit Event")));

	if(isset($vars['eid'])) {
		$form->add_hidden('eid', $vars['eid']);
		$events = $phpcdb->get_occurrences_by_eid($vars['eid']);
		// FIXME make some way to add multiple occurrences,
		//  and then pre-fill that with the existing occurrences
		$event = $events[0];
		$defaults = array(
				'subject' => $event->get_raw_subject(),
				'description' => $event->get_raw_desc(),
				'start-year' => $event->get_year(),
				'end-year' => $event->get_end_year(),
				'start-month' => $event->get_month(),
				'end-month' => $event->get_end_month(),
				'start-day' => $event->get_day(),
				'end-day' => $event->get_end_day(),
				'start-hour' => $event->get_hour(),
				'start-minute' => $event->get_minute(),
				'end-hour' => $event->get_end_hour(),
				'end-minute' => $event->get_end_minute(),
				'readonly' => $event->is_readonly(),
				);
		if(!empty($event->catid))
			$defaults['catid'] = $event->catid;
	} else {
		$defaults = array(
				'start-year' => $year,
				'end-year' => $year,
				'start-month' => $month,
				'end-month' => $month,
				'start-day' => $day,
				'end-day' => $day,
				'start-hour' => 17,
				'start-minute' => 0,
				'end-hour' => 18,
				'end-minute' => 0,
				);
	}
        return $form->get_html($defaults);
}

?>
