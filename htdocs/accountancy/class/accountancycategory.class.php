<?php
/* Copyright (C) 2016		Jamal Elbaz			<jamelbaz@gmail.pro>
 * Copyright (C) 2016-2017	Alexandre Spangaro	<aspangaro@open-dsi.fr>
 * Copyright (C) 2018-2024	Frédéric France     <frederic.france@free.fr>
 * Copyright (C) 2024		MDW					<mdeweerd@users.noreply.github.com>
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
 * \file	htdocs/accountancy/class/accountancycategory.class.php
 * \ingroup Accountancy (Double entries)
 * \brief	File of class to manage categories of an accounting category_type
 */

// Class
require_once DOL_DOCUMENT_ROOT.'/core/lib/accounting.lib.php';

/**
 * Class to manage categories of an accounting account
 */
class AccountancyCategory // extends CommonObject
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/**
	 * @var string 		Error string
	 */
	public $error;

	/**
	 * @var string[] Error codes (or messages)
	 */
	public $errors = array();

	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'c_accounting_category';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'c_accounting_category';

	/**
	 * @var int ID
	 * @deprecated
	 */
	public $rowid;

	/**
	 * @var int ID
	 */
	public $id;

	/**
	 * @var string Accountancy code
	 */
	public $code;

	/**
	 * @var string Accountancy Category label
	 */
	public $label;

	/**
	 * @var string Accountancy range account
	 */
	public $range_account;

	/**
	 * @var int Sens of the account:  0: credit - debit, 1: debit - credit
	 */
	public $sens;

	/**
	 * @var int Category type of accountancy
	 */
	public $category_type;

	/**
	 * @var string Formula
	 */
	public $formula;

	/**
	 * @var int     Position
	 */
	public $position;

	/**
	 * @var int country id
	 */
	public $fk_country;

	/**
	 * @var int Is active
	 */
	public $active;

	/**
	 * @var Object[] Lines cptbk
	 */
	public $lines_cptbk;

	/**
	 * @var Object[] Lines display
	 */
	public $lines_display;

	/**
	 * @var mixed Sum debit credit
	 */
	public $sdc;

	/**
	 * @var array<string,float> Sum debit credit per month
	 */
	public $sdcpermonth;

	/**
	 * @var array<string,float> Sum debit credit per account
	 */
	public $sdcperaccount;

	/**
	 *  Constructor
	 *
	 *  @param      DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}


	/**
	 *  Create object into database
	 *
	 *  @param      User	$user        User that create
	 *  @param      int		$notrigger   0=launch triggers after, 1=disable triggers
	 *  @return     int      		   	 Return integer <0 if KO, Id of created object if OK
	 */
	public function create($user, $notrigger = 0)
	{
		global $conf, $langs;
		$error = 0;

		// Clean parameters
		if (isset($this->code)) {
			$this->code = trim($this->code);
		}
		if (isset($this->label)) {
			$this->label = trim($this->label);
		}
		if (isset($this->range_account)) {
			$this->range_account = trim($this->range_account);
		}
		if (isset($this->sens)) {
			$this->sens = (int) $this->sens;
		}
		if (isset($this->category_type)) {
			$this->category_type = (int) $this->category_type;
		}
		if (isset($this->formula)) {
			$this->formula = trim($this->formula);
		}
		if (isset($this->position)) {
			$this->position = (int) $this->position;
		}
		if (isset($this->fk_country)) {
			$this->fk_country = (int) $this->fk_country;
		}
		if (isset($this->active)) {
			$this->active = (int) $this->active;
		}

		// Check parameters
		// Put here code to add control on parameters values

		// Insert request
		$sql = "INSERT INTO ".$this->db->prefix().$this->table_element." (";
		if ($this->rowid > 0) {
			$sql .= "rowid, ";
		}
		$sql .= "code, ";
		$sql .= "label, ";
		$sql .= "range_account, ";
		$sql .= "sens, ";
		$sql .= "category_type, ";
		$sql .= "formula, ";
		$sql .= "position, ";
		$sql .= "fk_country, ";
		$sql .= "active, ";
		$sql .= "entity";
		$sql .= ") VALUES (";
		if ($this->rowid > 0) {
			$sql .= " ".((int) $this->rowid).",";
		}
		$sql .= " ".(!isset($this->code) ? "NULL" : "'".$this->db->escape($this->code)."'").",";
		$sql .= " ".(!isset($this->label) ? 'NULL' : "'".$this->db->escape($this->label)."'").",";
		$sql .= " ".(!isset($this->range_account) ? 'NULL' : "'".$this->db->escape($this->range_account)."'").",";
		$sql .= " ".(!isset($this->sens) ? 'NULL' : "'".$this->db->escape($this->sens)."'").",";
		$sql .= " ".(!isset($this->category_type) ? 'NULL' : "'".$this->db->escape($this->category_type)."'").",";
		$sql .= " ".(!isset($this->formula) ? 'NULL' : "'".$this->db->escape($this->formula)."'").",";
		$sql .= " ".(!isset($this->position) ? 'NULL' : ((int) $this->position)).",";
		$sql .= " ".(!isset($this->fk_country) ? 'NULL' : ((int) $this->fk_country)).",";
		$sql .= " ".(!isset($this->active) ? 'NULL' : ((int) $this->active));
		$sql .= ", ".((int) $conf->entity);
		$sql .= ")";

		$this->db->begin();

		dol_syslog(get_class($this)."::create", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (!$resql) {
			$error++;
			$this->errors[] = "Error ".$this->db->lasterror();
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			$this->db->rollback();
			return -1 * $error;
		} else {
			$this->db->commit();
			return $this->id;
		}
	}


	/**
	 *  Load object in memory from database
	 *
	 *  @param      int		$id    	Id object
	 *  @param		string	$code	Code
	 *  @param		string	$label	Label
	 *  @return     int          	Return integer <0 if KO, >0 if OK
	 */
	public function fetch($id, $code = '', $label = '')
	{
		$sql = "SELECT";
		$sql .= " t.rowid,";
		$sql .= " t.code,";
		$sql .= " t.label,";
		$sql .= " t.range_account,";
		$sql .= " t.sens,";
		$sql .= " t.category_type,";
		$sql .= " t.formula,";
		$sql .= " t.position,";
		$sql .= " t.fk_country,";
		$sql .= " t.active";
		$sql .= " FROM ".$this->db->prefix().$this->table_element." as t";
		if ($id) {
			$sql .= " WHERE t.rowid = ".((int) $id);
		} else {
			$sql .= " WHERE t.entity IN (".getEntity('c_accounting_category').")"; // Don't use entity if you use rowid
			if ($code) {
				$sql .= " AND t.code = '".$this->db->escape($code)."'";
			} elseif ($label) {
				$sql .= " AND t.label = '".$this->db->escape($label)."'";
			}
		}

		dol_syslog(get_class($this)."::fetch", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);

				$this->id            = $obj->rowid;
				$this->code          = $obj->code;
				$this->label         = $obj->label;
				$this->range_account = $obj->range_account;
				$this->sens          = $obj->sens;
				$this->category_type = $obj->category_type;
				$this->formula       = $obj->formula;
				$this->position      = $obj->position;
				$this->fk_country    = $obj->fk_country;
				$this->active        = $obj->active;
			}
			$this->db->free($resql);

			return 1;
		} else {
			$this->error = "Error ".$this->db->lasterror();
			return -1;
		}
	}


	/**
	 *  Update object into database
	 *
	 *  @param      User	$user        User that modify
	 *  @param      int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return     int     		   	 Return integer <0 if KO, >0 if OK
	 */
	public function update($user = null, $notrigger = 0)
	{
		global $conf, $langs;
		$error = 0;

		// Clean parameters
		if (isset($this->code)) {
			$this->code = trim($this->code);
		}
		if (isset($this->label)) {
			$this->label = trim($this->label);
		}
		if (isset($this->range_account)) {
			$this->range_account = trim($this->range_account);
		}
		if (isset($this->sens)) {
			$this->sens = (int) $this->sens;
		}
		if (isset($this->category_type)) {
			$this->category_type = (int) $this->category_type;
		}
		if (isset($this->formula)) {
			$this->formula = trim($this->formula);
		}
		if (isset($this->position)) {
			$this->position = (int) $this->position;
		}
		if (isset($this->fk_country)) {
			$this->fk_country = (int) $this->fk_country;
		}
		if (isset($this->active)) {
			$this->active = (int) $this->active;
		}


		// Check parameters
		// Put here code to add control on parameters values

		// Update request
		$sql = "UPDATE ".$this->db->prefix().$this->table_element." SET";
		$sql .= " code=".(isset($this->code) ? "'".$this->db->escape($this->code)."'" : "null").",";
		$sql .= " label=".(isset($this->label) ? "'".$this->db->escape($this->label)."'" : "null").",";
		$sql .= " range_account=".(isset($this->range_account) ? "'".$this->db->escape($this->range_account)."'" : "null").",";
		$sql .= " sens=".(isset($this->sens) ? ((int) $this->sens) : "null").",";
		$sql .= " category_type=".(isset($this->category_type) ? ((int) $this->category_type) : "null").",";
		$sql .= " formula=".(isset($this->formula) ? "'".$this->db->escape($this->formula)."'" : "null").",";
		$sql .= " position=".(isset($this->position) ? ((int) $this->position) : "null").",";
		$sql .= " fk_country=".(isset($this->fk_country) ? ((int) $this->fk_country) : "null").",";
		$sql .= " active=".(isset($this->active) ? ((int) $this->active) : "null");
		$sql .= " WHERE rowid=".((int) $this->id);

		$this->db->begin();

		dol_syslog(get_class($this)."::update", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (!$resql) {
			$error++;
			$this->errors[] = "Error ".$this->db->lasterror();
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			$this->db->rollback();
			return -1 * $error;
		} else {
			$this->db->commit();
			return 1;
		}
	}


	/**
	 *  Delete object in database
	 *
	 *	@param  User	$user        User that delete
	 *  @param	int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return	int					 Return integer <0 if KO, >0 if OK
	 */
	public function delete($user, $notrigger = 0)
	{
		global $conf, $langs;
		$error = 0;

		$sql = "DELETE FROM ".$this->db->prefix().$this->table_element;
		$sql .= " WHERE rowid=".((int) $this->id);

		$this->db->begin();

		dol_syslog(get_class($this)."::delete", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (!$resql) {
			$error++;
			$this->errors[] = "Error ".$this->db->lasterror();
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			$this->db->rollback();
			return -1 * $error;
		} else {
			$this->db->commit();
			return 1;
		}
	}


	/**
	 * Function to select into ->lines_display all accounting accounts for a given custom accounting group
	 *
	 * @param 	int 	$id 	Id
	 * @return 	int 			Return integer <0 if KO, 0 if not found, >0 if OK
	 */
	public function display($id)
	{
		global $conf;
		$sql = "SELECT t.rowid, t.account_number, t.label";
		$sql .= " FROM ".$this->db->prefix()."accounting_account as t";
		$sql .= " WHERE t.fk_accounting_category = ".((int) $id);
		$sql .= " AND t.entity = ".$conf->entity;

		$this->lines_display = array();

		dol_syslog(__METHOD__, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			if ($num) {
				while ($obj = $this->db->fetch_object($resql)) {
					$this->lines_display[] = $obj;
				}
			}
			return $num;
		} else {
			$this->error = "Error ".$this->db->lasterror();
			$this->errors[] = $this->error;
			dol_syslog(__METHOD__." ".implode(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Function to fill ->lines_cptbk with accounting account (defined in chart of account) and not yet into a custom group
	 *
	 * @param 	int $id     Id of category to know which account to exclude
	 * @return 	int 		Return integer <0 if KO, 0 if not found, >0 if OK
	 */
	public function getAccountsWithNoCategory($id)
	{
		global $conf;

		$sql = "SELECT aa.account_number as numero_compte, aa.label as label_compte";
		$sql .= " FROM ".$this->db->prefix()."accounting_account as aa";
		$sql .= " INNER JOIN ".$this->db->prefix()."accounting_system as asy ON aa.fk_pcg_version = asy.pcg_version";
		$sql .= " WHERE (aa.fk_accounting_category <> ".((int) $id)." OR aa.fk_accounting_category IS NULL)";
		$sql .= " AND asy.rowid = ".((int) getDolGlobalInt('CHARTOFACCOUNTS'));
		$sql .= " AND aa.active = 1";
		$sql .= " AND aa.entity = ".$conf->entity;
		$sql .= " GROUP BY aa.account_number, aa.label";
		$sql .= " ORDER BY aa.account_number, aa.label";

		$this->lines_cptbk = array();

		dol_syslog(__METHOD__, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			if ($num) {
				while ($obj = $this->db->fetch_object($resql)) {
					$this->lines_cptbk[] = $obj;
				}
			}

			return $num;
		} else {
			$this->error = "Error ".$this->db->lasterror();
			$this->errors[] = $this->error;
			dol_syslog(__METHOD__." ".implode(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Function to add an accounting account in an accounting category
	 *
	 * @param int					$id_cat		Id category
	 * @param array<string,?string> $cpts		list of accounts array
	 *
	 * @return int Return integer <0 if KO, >0 if OK
	 */
	public function updateAccAcc($id_cat, $cpts = array())
	{
		global $conf;
		$error = 0;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/accounting.lib.php';

		$sql = "SELECT aa.rowid, aa.account_number";
		$sql .= " FROM ".$this->db->prefix()."accounting_account as aa";
		$sql .= " INNER JOIN ".$this->db->prefix()."accounting_system as asy ON aa.fk_pcg_version = asy.pcg_version";
		$sql .= " AND asy.rowid = ".((int) getDolGlobalInt('CHARTOFACCOUNTS'));
		$sql .= " AND aa.active = 1";
		$sql .= " AND aa.entity = ".$conf->entity;
		$sql .= " ORDER BY LENGTH(aa.account_number) DESC;"; // LENGTH is ok with mysql and postgresql

		$this->db->begin();

		dol_syslog(__METHOD__, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (!$resql) {
			$error++;
			$this->errors[] = "Error ".$this->db->lasterror();
			$this->db->rollback();
			return -1;
		}

		$accountincptsadded = array();
		while ($obj = $this->db->fetch_object($resql)) {
			$account_number_formated = length_accountg($obj->account_number);
			if (!empty($accountincptsadded[$account_number_formated])) {
				continue;
			}

			if (array_key_exists($account_number_formated, $cpts)) {
				$accountincptsadded[$account_number_formated] = 1;
				// We found an account number that is in list $cpts of account to add
				$sql = "UPDATE ".$this->db->prefix()."accounting_account";
				$sql .= " SET fk_accounting_category=".((int) $id_cat);
				$sql .= " WHERE rowid=".((int) $obj->rowid);
				dol_syslog(__METHOD__, LOG_DEBUG);
				$resqlupdate = $this->db->query($sql);
				if (!$resqlupdate) {
					$error++;
					$this->errors[] = "Error ".$this->db->lasterror();
				}
			}
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(__METHOD__." ".$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			$this->db->rollback();

			return -1 * $error;
		} else {
			$this->db->commit();

			return 1;
		}
	}

	/**
	 * Function to delete an accounting account from an accounting category
	 *
	 * @param int $cpt_id Id of accounting account
	 *
	 * @return int Return integer <0 if KO, >0 if OK
	 */
	public function deleteCptCat($cpt_id)
	{
		$error = 0;

		$sql = "UPDATE ".$this->db->prefix()."accounting_account as aa";
		$sql .= " SET fk_accounting_category= 0";
		$sql .= " WHERE aa.rowid = ".((int) $cpt_id);
		$this->db->begin();

		dol_syslog(__METHOD__, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (!$resql) {
			$error++;
			$this->errors[] = "Error ".$this->db->lasterror();
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(__METHOD__." ".$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			$this->db->rollback();

			return -1 * $error;
		} else {
			$this->db->commit();

			return 1;
		}
	}

	/**
	 * Function to set the property ->sdc (and ->sdcperaccount) that is the result of an accounting account from the ledger with a direction and a period
	 *
	 * @param int|array<?string>	$cpt 	Accounting account or array of accounting account
	 * @param int 		$date_start			Date start
	 * @param int	 	$date_end			Date end
	 * @param int<0,1>	$sens 				Sens of the account:  0: credit - debit (use this by default), 1: debit - credit
	 * @param string	$thirdparty_code	Third party code
	 * @param int       $month 				Specific month - Can be empty
	 * @param int       $year 				Specific year - Can be empty
	 * @return integer 						Return integer <0 if KO, >= 0 if OK
	 */
	public function getSumDebitCredit($cpt, $date_start, $date_end, $sens, $thirdparty_code = 'nofilter', $month = 0, $year = 0)
	{
		global $conf;

		$this->sdc = 0;
		$this->sdcpermonth = array();

		$listofaccount = '';

		if (is_array($cpt)) {
			foreach ($cpt as $cptcursor) {
				if (! is_null($cptcursor)) {
					if ($listofaccount) {
						$listofaccount .= ",";
					}
					$listofaccount .= "'".$cptcursor."'";
				}
			}
			if (empty($listofaccount)) {
				// List of account is empty, so we do no try sql request, we can say result is empty.
				return 0;
			}
		}

		$sql = "SELECT SUM(t.debit) as debit, SUM(t.credit) as credit";
		if (is_array($cpt)) {
			$sql .= ", t.numero_compte as accountancy_account";
		}
		$sql .= " FROM ".$this->db->prefix()."accounting_bookkeeping as t";
		//if (in_array($this->db->type, array('mysql', 'mysqli'))) $sql.=' USE INDEX idx_accounting_bookkeeping_doc_date';
		$sql .= " WHERE t.entity = ".((int) $conf->entity);
		if (is_array($cpt)) {
			$sql .= " AND t.numero_compte IN (".$this->db->sanitize($listofaccount, 1).")";
		} else {
			$sql .= " AND t.numero_compte = '".$this->db->escape($cpt)."'";
		}
		if (!empty($date_start) && !empty($date_end) && (empty($month) || empty($year))) {	// If month/year provided, it is stronger than filter date_start/date_end
			$sql .= " AND (t.doc_date BETWEEN '".$this->db->idate($date_start)."' AND '".$this->db->idate($date_end)."')";
		}
		if (!empty($month) && !empty($year)) {
			$sql .= " AND (t.doc_date BETWEEN '".$this->db->idate(dol_get_first_day($year, $month))."' AND '".$this->db->idate(dol_get_last_day($year, $month))."')";
		}
		if ($thirdparty_code != 'nofilter') {
			$sql .= " AND t.thirdparty_code = '".$this->db->escape($thirdparty_code)."'";
		}
		if (is_array($cpt)) {
			$sql .= " GROUP BY t.numero_compte";
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			if ($num) {
				$i = 0;
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					if ($obj) {
						if ($sens == 1) {
							$this->sdc = $obj->debit - $obj->credit;
						} else {
							$this->sdc = $obj->credit - $obj->debit;
						}
						if (is_array($cpt)) {
							$this->sdcperaccount[$obj->accountancy_account] = $this->sdc;
						}
					}
					$i++;
				}
			}

			return $num;
		} else {
			$this->error = "Error ".$this->db->lasterror();
			$this->errors[] = $this->error;
			dol_syslog(__METHOD__." ".$this->error, LOG_ERR);
			return -1;
		}
	}

	/**
	 * Function to get an array of all active custom groups (llx_c_accunting_categories) with their accounts from the chart of account (ll_accounting_acount)
	 *
	 * @param	int				$catid		Custom group ID
	 * @return array<string,array<int,array{id:int,code:string,label:string,position:string,category_type:string,formula:string,sens:string,dc:string,account_number:string,account_label:string}>>|int<-1,-1>   		    Result in table (array), -1 if KO
	 * @see getCats(), getCptsCat()
	 */
	public function getCatsCpts($catid = 0)
	{
		global $mysoc, $conf;

		if (empty($mysoc->country_id)) {
			$this->error = "Error ".$this->db->lasterror();
			dol_syslog(__METHOD__." ".$this->error, LOG_ERR);
			return -1;
		}

		$sql = "SELECT t.rowid, t.account_number, t.label as account_label,";
		$sql .= " cat.code, cat.position, cat.label as name_cat, cat.sens, cat.category_type, cat.formula";
		$sql .= " FROM ".$this->db->prefix()."accounting_account as t, ".$this->db->prefix()."c_accounting_category as cat";
		$sql .= " WHERE t.fk_accounting_category IN (SELECT c.rowid";
		$sql .= " FROM ".$this->db->prefix().$this->table_element." as c";
		$sql .= " WHERE c.active = 1";
		$sql .= " AND c.entity = ".$conf->entity;
		$sql .= " AND (c.fk_country = ".((int) $mysoc->country_id)." OR c.fk_country = 0)";
		$sql .= " AND cat.rowid = t.fk_accounting_category";
		$sql .= " AND t.entity = ".$conf->entity;
		if ($catid > 0) {
			$sql .= " AND cat.rowid = ".((int) $catid);
		}
		$sql .= " ORDER BY cat.position ASC";

		$resql = $this->db->query($sql);
		if ($resql) {
			$obj = '';
			$num = $this->db->num_rows($resql);
			$data = array();
			if ($num) {
				while ($obj = $this->db->fetch_object($resql)) {
					$name_cat = $obj->name_cat;
					$data[$name_cat][$obj->rowid] = array(
						'id' => $obj->rowid,
						'code' => $obj->code,
						'label' => $obj->label,
						'position' => $obj->position,
						'category_type' => $obj->category_type,
						'formula' => $obj->formula,
						'sens' => $obj->sens,
						'dc' => $obj->sens,
						'account_number' => $obj->account_number,
						'account_label' => $obj->account_label
					);
				}
			}
			return $data;
		} else {
			$this->error = "Error ".$this->db->lasterror();
			dol_syslog(__METHOD__." ".$this->error, LOG_ERR);
			return -1;
		}
	}

	/**
	 * Return list of custom groups.
	 * For list + detail of accounting account, see getCatsCpt()
	 *
	 * @param	int			$categorytype		-1=All, 0=Only non computed groups, 1=Only computed groups
	 * @param	int			$active				1= active, 0=not active
	 * @param	int			$id_report			id of the report
	 * @return	never|array<array{rowid:string,code:string,label:string,formula:string,position:string,category_type:string,sens:string,dc:string}>|int	Array of groups or -1 if error
	 * @see getCatsCpts(), getCptsCat()
	 */
	public function getCats($categorytype = -1, $active = 1, $id_report = 1)
	{
		global $conf, $mysoc;

		if (empty($mysoc->country_id)) {
			dol_print_error(null, 'Call to getCats with mysoc country not yet defined');
			exit();
		}

		$sql = "SELECT c.rowid, c.code, c.label, c.formula, c.position, c.category_type, c.sens, c.fk_report";
		$sql .= " FROM ".$this->db->prefix().$this->table_element." as c";
		$sql .= " WHERE c.active = " . (int) $active;
		$sql .= " AND c.fk_report=".((int) $id_report);
		$sql .= " AND c.entity = ".$conf->entity;
		if ($categorytype >= 0) {
			$sql .= " AND c.category_type = 1";
		}
		$sql .= " AND (c.fk_country = ".((int) $mysoc->country_id)." OR c.fk_country = 0)";
		$sql .= " ORDER BY c.position ASC";

		$resql = $this->db->query($sql);
		if ($resql) {
			$i = 0;
			$obj = '';
			$num = $this->db->num_rows($resql);
			$data = array();
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);

					$data[] = array(
							'rowid' => $obj->rowid,
							'code' => $obj->code,
							'label' => $obj->label,
							'position' => $obj->position,
							'category_type' => $obj->category_type,
							'formula' => $obj->formula,
							'sens' => $obj->sens,
							'dc' => $obj->sens
					);
					$i++;
				}
			}
			return $data;
		} else {
			$this->error = "Error ".$this->db->lasterror();
			$this->errors[] = $this->error;
			dol_syslog(__METHOD__." ".implode(',', $this->errors), LOG_ERR);

			return -1;
		}
	}


	/**
	 * Get all accounting account of a given custom group (or a list of custom groups).
	 * You must choose between first parameter (personalized group) or the second (free criteria filter)
	 *
	 * @param 	int 		$cat_id 				Id if personalized accounting group/category
	 * @param 	string 		$predefinedgroupwhere 	Sql criteria filter to select accounting accounts. This value must be sanitized and not come from an input of a user.
	 * 												Example: "pcg_type = 'EXPENSE' AND fk_pcg_version = 'xx'"
	 * 												Example: "fk_accounting_category = 99"
	 * @return	never|array<array{id:int,account_number:string,account_label:string}>|int<-1,-1>		Array of accounting accounts or -1 if error
	 * @see getCats(), getCatsCpts()
	 */
	public function getCptsCat($cat_id, $predefinedgroupwhere = '')
	{
		global $conf, $mysoc;
		$sql = '';

		if (empty($mysoc->country_id) && empty($mysoc->country_code)) {
			dol_print_error(null, 'Call to select_accounting_account with mysoc country not yet defined');
			exit();
		}

		$pcgverid = getDolGlobalInt('CHARTOFACCOUNTS');
		$pcgvercode = dol_getIdFromCode($this->db, (string) $pcgverid, 'accounting_system', 'rowid', 'pcg_version');
		if (empty($pcgvercode)) {
			$pcgvercode = $pcgverid;
		}

		if (!empty($cat_id)) {
			$sql = "SELECT t.rowid, t.account_number, t.label as account_label";
			$sql .= " FROM ".$this->db->prefix()."accounting_account as t";
			$sql .= " WHERE t.fk_accounting_category = ".((int) $cat_id);
			$sql .= " AND t.entity = ".$conf->entity;
			$sql .= " AND t.active = 1";
			$sql .= " AND t.fk_pcg_version = '".$this->db->escape($pcgvercode)."'";
			$sql .= " ORDER BY t.account_number";
		} else {
			$sql = "SELECT t.rowid, t.account_number, t.label as account_label";
			$sql .= " FROM ".$this->db->prefix()."accounting_account as t";
			$sql .= " WHERE ".$predefinedgroupwhere;
			$sql .= " AND t.entity = ".$conf->entity;
			$sql .= ' AND t.active = 1';
			$sql .= " AND t.fk_pcg_version = '".$this->db->escape($pcgvercode)."'";
			$sql .= " ORDER BY t.account_number";
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$i = 0;
			$obj = '';
			$num = $this->db->num_rows($resql);
			$data = array();
			if ($num) {
				while ($obj = $this->db->fetch_object($resql)) {
					$data[] = array(
							'id' => $obj->rowid,
							'account_number' => $obj->account_number,
							'account_label' => $obj->account_label,
					);
					$i++;
				}
			}
			return $data;
		} else {
			$this->error = "Error ".$this->db->lasterror();
			dol_syslog(__METHOD__." ".$this->error, LOG_ERR);

			return -1;
		}
	}
}
