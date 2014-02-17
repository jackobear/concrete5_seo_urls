<?php
defined('C5_EXECUTE') or die("Access Denied.");
class Page extends Concrete5_Model_Page {

// This override removs parent url slugs when determining the url for a page


	function rescanCollectionPath($retainOldPagePath = false) {
		if ($this->cParentID > 0) {
			$db = Loader::db();
			// first, we grab the path of the parent, if such a thing exists, for our prefix
			$q = "select PagePaths.cPath as cPathParent from PagePaths left join Pages on (Pages.cParentID = PagePaths.cID and PagePaths.ppIsCanonical = 1) where Pages.cID = '{$this->cID}'";
			$path = $db->getOne($q);
			$cPath = ""; // Don't include parents in url slug unless it is a dashboard page
			if(strpos($path, "dashboard") !== FALSE){
				$cPath = $path;
			}

			// Now we perform the collection path function on the current cID
			$np = $this->rescanCollectionPathIndividual($this->cID, $cPath, $retainOldPagePath);
			if(strpos($np, "dashboard") !== FALSE){
				$this->cPath = $np;
			}else{
				$this->cPath = "";
			}

			// Now we start with the recursive collection path scanning, armed with our prefix (from the level above what we're scanning)
			if ($np) {
				if(strpos($np, "dashboard") !== FALSE){
					$this->rescanCollectionPathChildren($this->cID, $np);
				}else{
					$this->rescanCollectionPathChildren($this->cID, "");  // Removed $np to remove parent from url slug
				}
			}
		}
	}
	function addCollectionAlias($c) {
		$db = Loader::db();
		// the passed collection is the parent collection
		$cParentID = $c->getCollectionID();

		$u = new User();
		$uID = $u->getUserID();
		$ctID = 0;

		$dh = Loader::helper('date');

		$cDate = $dh->getSystemDateTime();
		$cDatePublic = $dh->getSystemDateTime();
		$handle = $this->getCollectionHandle();

		$_cParentID = $c->getCollectionID();
		$q = "select PagePaths.cPath from PagePaths where cID = '{$_cParentID}'";
		if ($_cParentID > 1) {
			$q .=  " and ppIsCanonical = 1";
		}
		$path = $db->getOne($q);
		$cPath = ""; // Don't include parents in url slug unless it is a dashboard page
		if(strpos($path, "dashboard") !== FALSE){
			$cPath = $path;
		}

		$data['handle'] = $this->getCollectionHandle();
		$data['name'] = $this->getCollectionName();

		$cobj = parent::add($data);
		$newCID = $cobj->getCollectionID();

		$v = array($newCID, $cParentID, $uID, $this->getCollectionID());
		$q = "insert into Pages (cID, cParentID, uID, cPointerID) values (?, ?, ?, ?)";
		$r = $db->prepare($q);

		$res = $db->execute($r, $v);
		$newCID = $db->Insert_ID();

		Loader::model('page_statistics');
		PageStatistics::incrementParents($newCID);


		$q2 = "insert into PagePaths (cID, cPath) values (?, ?)";
		$v2 = array($newCID, $cPath . '/' . $handle);
		$db->query($q2, $v2);


		return $newCID;
	}

	function rescanCollectionPathChildren($cID, $cPath) {
		$db = Loader::db();
		$q = "select cID from Pages where cParentID = $cID";
		$r = $db->query($q);
		if ($r) {
			while ($row = $r->fetchRow()) {
				$np = $this->rescanCollectionPathIndividual($row['cID'], $cPath);
				if(strpos($np, "dashboard") !== FALSE){
					$this->rescanCollectionPathChildren($row['cID'], $np);
				}else{
					$this->rescanCollectionPathChildren($row['cID'], "");  // Removed $np to remove parent from url slug
				}
			}
			$r->free();
		}
	}

}