<?php
/* Copyright (C) 2001-2002  Rodolphe Quiedeville    <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012  Regis Houssin           <regis.houssin@inodbox.com>
 * Copyright (C) 2019       Nicolas ZABOURI         <info@inovea-conseil.com>
 * Copyright (C) 2024		MDW							<mdeweerd@users.noreply.github.com>
 * Copyright (C) 2024       Frédéric France         <frederic.france@free.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *  \file       htdocs/don/index.php
 *  \ingroup    donations
 *  \brief      Home page of donation module
 */

// Load Dolibarr environment
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/don/class/don.class.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

$langs->load("donations");


// Initialize a technical object to manage hooks. Note that conf->hooks_modules contains array
$hookmanager->initHooks(array('donationindex'));

$donation_static = new Don($db);

// Security check
$result = restrictedArea($user, 'don');


/*
 * Actions
 */

// None


/*
 * View
 */

$donstatic = new Don($db);

$help_url = 'EN:Module_Donations|FR:Module_Dons|ES:M&oacute;dulo_Donaciones|DE:Modul_Spenden';

llxHeader('', $langs->trans("Donations"), $help_url, '', 0, 0, '', '', '', 'mod-donation page-index');

$nb = array();
$somme = array();
$total = 0;

$sql = "SELECT count(d.rowid) as nb, sum(d.amount) as somme , d.fk_statut";
$sql .= " FROM ".MAIN_DB_PREFIX."don as d WHERE d.entity IN (".getEntity('donation').")";
$sql .= " GROUP BY d.fk_statut";
$sql .= " ORDER BY d.fk_statut";

$result = $db->query($sql);
if ($result) {
	$i = 0;
	$num = $db->num_rows($result);
	while ($i < $num) {
		$objp = $db->fetch_object($result);

		$somme[$objp->fk_statut] = $objp->somme;
		$nb[$objp->fk_statut] = $objp->nb;
		$total += $objp->somme;

		$i++;
	}
	$db->free($result);
} else {
	dol_print_error($db);
}

print load_fiche_titre($langs->trans("DonationsArea"), '', 'object_donation');


print '<div class="fichecenter"><div class="fichethirdleft">';

if (!isset($listofsearchfields) || !is_array($listofsearchfields)) {
	// Ensure $listofsearchfields is an array
	$listofsearchfields = array();
}
if (getDolGlobalString('MAIN_SEARCH_FORM_ON_HOME_AREAS')) {     // TODO Add a search into global search combo so we can remove this
	if (isModEnabled('don') && $user->hasRight('don', 'lire')) {
		$listofsearchfields['search_donation'] = array('text' => 'Donation');
	}

	if (count($listofsearchfields)) {
		print '<form method="post" action="'.DOL_URL_ROOT.'/core/search.php">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<table class="noborder nohover centpercent">';
		$i = 0;
		foreach ($listofsearchfields as $key => $value) {
			if ($i == 0) {
				print '<tr class="liste_titre"><td colspan="3">'.$langs->trans("Search").'</td></tr>';
			}
			print '<tr>';
			print '<td class="nowrap"><label for="'.$key.'">'.$langs->trans($value["text"]).'</label></td><td><input type="text" class="flat inputsearch" name="'.$key.'" id="'.$key.'"></td>';
			if ($i == 0) {
				print '<td rowspan="'.count($listofsearchfields).'"><input type="submit" class="button" value="'.$langs->trans("Search").'"></td>';
			}
			print '</tr>';
			$i++;
		}
		print '</table>';
		print '</form>';
		print '<br>';
	}
}

$dataseries = array();
$colorseries = array();

include DOL_DOCUMENT_ROOT.'/theme/'.$conf->theme.'/theme_vars.inc.php';

print '<table class="noborder nohover centpercent">';
print '<tr class="liste_titre">';
print '<th colspan="4">'.$langs->trans("Statistics").'</th>';
print "</tr>\n";

$listofstatus = array(0, 1, -1, 2);
foreach ($listofstatus as $status) {
	$dataseries[] = array($donstatic->LibStatut($status, 1), (isset($nb[$status]) ? (int) $nb[$status] : 0));
	if ($status == Don::STATUS_DRAFT) {
		$colorseries[$status] = '-'.$badgeStatus0;
	}
	if ($status == Don::STATUS_VALIDATED) {
		$colorseries[$status] = $badgeStatus1;
	}
	if ($status == Don::STATUS_CANCELED) {
		$colorseries[$status] = $badgeStatus9;
	}
	if ($status == Don::STATUS_PAID) {
		$colorseries[$status] = $badgeStatus6;
	}
}

if ($conf->use_javascript_ajax) {
	print '<tr><td class="center" colspan="4">';

	include_once DOL_DOCUMENT_ROOT.'/core/class/dolgraph.class.php';
	$dolgraph = new DolGraph();
	$dolgraph->SetData($dataseries);
	$dolgraph->SetDataColor(array_values($colorseries));
	$dolgraph->setShowLegend(2);
	$dolgraph->setShowPercent(1);
	$dolgraph->SetType(array('pie'));
	$dolgraph->setHeight('200');
	$dolgraph->draw('idgraphstatus');
	print $dolgraph->show($total ? 0 : 1);

	print '</td></tr>';
}

print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Status").'</td>';
print '<td class="right">'.$langs->trans("Number").'</td>';
print '<td class="right">'.$langs->trans("Total").'</td>';
print '<td class="right">'.$langs->trans("Average").'</td>';
print '</tr>';

$total = 0;
$totalnb = 0;
foreach ($listofstatus as $status) {
	print '<tr class="oddeven">';
	print '<td><a href="list.php?search_status='.$status.'">'.$donstatic->LibStatut($status, 4).'</a></td>';
	print '<td class="right">'.(!empty($nb[$status]) ? $nb[$status] : '&nbsp;').'</td>';
	print '<td class="right nowraponall amount">'.(!empty($nb[$status]) ? price($somme[$status], 1, '', 1, -1, 'MT') : '&nbsp;').'</td>';
	print '<td class="right nowraponall">'.(!empty($nb[$status]) ? price(price2num($somme[$status] / $nb[$status], 'MT')) : '&nbsp;').'</td>';
	$totalnb += (!empty($nb[$status]) ? $nb[$status] : 0);
	$total += (!empty($somme[$status]) ? $somme[$status] : 0);
	print "</tr>";
}

print '<tr class="liste_total">';
print '<td>'.$langs->trans("Total").'</td>';
print '<td class="right nowraponall">'.$totalnb.'</td>';
print '<td class="right nowraponall">'.price($total, 1, "", 1, -1, 'MT').'</td>';
print '<td class="right nowraponall">'.($totalnb ? price(price2num($total / $totalnb, 'MT')) : '&nbsp;').'</td>';
print '</tr>';
print "</table>";


print '</div><div class="fichetwothirdright">';


$max = getDolGlobalInt('MAIN_SIZE_SHORTLIST_LIMIT', 5);


/*
 * Last modified donations
 */

$sql = "SELECT c.rowid, c.ref, c.fk_statut, c.societe, c.lastname, c.firstname, c.tms as datem, c.amount, c.fk_soc as socid";
$sql .= " FROM ".MAIN_DB_PREFIX."don as c";
$sql .= " WHERE c.entity IN (".getEntity("don").")";
//$sql.= " AND c.fk_statut > 2";
$sql .= " ORDER BY c.tms DESC";
$sql .= $db->plimit($max, 0);

$resql = $db->query($sql);
if ($resql) {
	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre">';
	print '<th colspan="5">'.$langs->trans("LastModifiedDonations", $max).' ';
	print '<a href="'.DOL_URL_ROOT.'/don/list.php?sortfield=d.datedon&sortorder=DESC">';
	print '<span class="badge">...</span>';
	print '</a>';
	print '</th></tr>';

	$num = $db->num_rows($resql);
	if ($num) {
		$i = 0;
		while ($i < $num) {
			$obj = $db->fetch_object($resql);

			print '<tr class="oddeven">';

			$donation_static->id = $obj->rowid;
			$donation_static->ref = $obj->ref ? $obj->ref : $obj->rowid;

			print '<td width="96" class="nobordernopadding nowrap">';
			print $donation_static->getNomUrl(1);
			print '</td>';

			print '<td class="nobordernopadding">';
			if (!empty($obj->socid)) {
				$companystatic = new Societe($db);
				$ret = $companystatic->fetch($obj->socid);
				if ($ret > 0) {
					print $companystatic->getNomUrl(1);
				}
			} else {
				print $obj->societe;
				print($obj->societe && ($obj->lastname || $obj->firstname) ? ' / ' : '');
				print dolGetFirstLastname($obj->firstname, $obj->lastname);
			}
			print '</td>';

			print '<td class="right nobordernopadding nowraponall amount">';
			print price($obj->amount, 1);
			print '</td>';

			// Date
			print '<td class="center" title="'.$langs->trans("DonationDate").': '.dol_print_date($db->jdate($obj->datem), 'day').'">'.dol_print_date($db->jdate($obj->datem), 'day').'</td>';

			print '<td class="right">'.$donation_static->LibStatut($obj->fk_statut, 5).'</td>';

			print '</tr>';
			$i++;
		}
	}
	print "</table><br>";
} else {
	dol_print_error($db);
}


print '</div></div>';

$parameters = array('user' => $user);
$reshook = $hookmanager->executeHooks('dashboardDonation', $parameters, $object); // Note that $action and $object may have been modified by hook

llxFooter();

$db->close();
