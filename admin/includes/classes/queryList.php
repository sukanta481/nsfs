<?php


	/**
	** Author: Oliver Susano (vher_98@yahoo.com)
	** Class queryList
	** Query with paginations and previous next links.
	** syntax: queryList($sql, $link, $page, [$rowsPerPage [, $pageLimit ]])
	
	Return the variables:

		$this->result = The page browser
		$this->start  = First result on this page
		$this->start  = Last result on this page
		$this->total  = Total results on this page
		$this->pages  = Number of pages
		$this->sql	  = The query to get the actual page

	**/

	class queryList {

		function __construct($sql, $link, $page, $rowsPerPage='10', $pageLimit='5') {

			// check the numbers of pages
			$result		= mysql_query($sql);
			$totalRows	= mysql_num_rows($result);
			$totalPages = ceil($totalRows / $rowsPerPage);


			// verify the given values
			$page = $page*1; $rowsPerPage = $rowsPerPage*1; $pageLimit = $pageLimit*1; 
			if(!is_int($rowsPerPage) || $rowsPerPage < 1) { $rowsPerPage = 10; }
			if(!is_int($pageLimit)   || $pageLimit   < 1) { $pageLimit   = 10; }
			if($page > $totalPages) { $page = $totalPages; }
			if(!is_int($page) || $page < 1) { $page = 1; }


			// build the starting values
			if($totalPages > $pageLimit ) { $value = $pageLimit; } 
				else { $value = $totalPages; }
			if($page > $pageLimit) { $i = $page - $pageLimit; $value = $pageLimit+$i; }
			$pages = "";


			// section for Previous Record
			if($page > 1){
				$pages .= '&nbsp;<b>[ <a href="'.$link.'&page='.($page-1).'">prev</a> ] &nbsp;</b>';
			}


			// build the Pages Browser
			while ($i < $value){
				$i++;
				if ($i == $page){
					$pages .= "<b>[ $i ]</b> ";
				} else { 
					if($i <= $totalPages) { $pages .= '<a href="'.$link."&page=$i\">$i</a> "; }
				}
			}


			// section for Next Record
			if($i <= $totalPages){
				if($totalPages != $page){
					$pages .= '&nbsp;<b>[ <a href="'.$link.'&page='.($page+1).'">next</a> ]</b>';
				}
			}


			// make the return values
			$this->result = $pages;
			$this->start  = (($page-1) * $rowsPerPage) + 1;
			$this->total  = $totalRows;
			$this->pages  = $totalPages;
			$this->sql	  = $sql." LIMIT ".($page-1)*$rowsPerPage.",".$rowsPerPage;
			if($page==$totalPages) 
				 { $this->stop = ($page-1)*$rowsPerPage+($totalRows-(($page-1)*$rowsPerPage)); }
			else { $this->stop = $page * $rowsPerPage; }

		} // end of query()
		
	} // end of Class


?>