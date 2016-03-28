<?php
class CedarUserList extends SpecialPage
{
    function CedarUserList()
    {
	SpecialPage::SpecialPage("CedarUserList");
	#wfLoadExtensionMessages( 'CedarUserList' ) ;
    }
    
    function execute( $par )
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgUser, $wgServer ;
	
	$this->setHeaders();

	$action = $wgRequest->getText( 'action' ) ;
	if( $action == 'block' )
	{
	    $this->blockUser() ;
	    return ;
	}
	else if( $action == 'reset' )
	{
	    $this->resetPassword() ;
	    return ;
	}
	else if( $action == 'edit' )
	{
	    $this->editUser() ;
	    return ;
	}
	else if( $action == 'modify' )
	{
	    $this->modifyUser() ;
	    return ;
	}
	else
	{
	    $this->user_list() ;
	}
    }

    function user_list()
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgUser, $wgServer ;
	
	// Grab the database information for mwbb
	$dbw =& wfGetDB( DB_MASTER );
	$cedar_user_table = $dbw->tableName( 'cedar_user_info' );
	$user_table = $dbw->tableName( 'user' );

	$where_clause = "" ;
	$search_for = $dbw->strencode( $wgRequest->getText( 'search_for') ) ;
	if( $search_for != "" )
	{
	    $search_by = $wgRequest->getText('search_by') ;
	    if( $search_by == "username" )
	    {
		$search_by = "ucase(u.user_name)" ;
		$search_for = strtoupper( $search_for ) ;
		$search_for = "like '%$search_for%'" ;
	    }
	    else if( $search_by == "realname" )
	    {
		$search_by = "ucase(u.user_real_name)" ;
		$search_for = strtoupper( $search_for ) ;
		$search_for = "like '%$search_for%'" ;
	    }
	    else if( $search_by == "org" )
	    {
		$search_by = "ucase(c.organization)" ;
		$search_for = strtoupper( $search_for ) ;
		$search_for = "like '%$search_for%'" ;
	    }
	    else if( $search_by == "email" )
	    {
		$search_by = "ucase(u.user_email)" ;
		$search_for = strtoupper( $search_for ) ;
		$search_for = "= '$search_for'" ;
	    }
	    else if( $search_by == "status" )
	    {
		$search_by = "lcase(c.status)" ;
		$search_for = strtolower( $search_for ) ;
		$search_for = "= '$search_for'" ;
	    }
	    else
	    {
		$search_by = "ucase(u.user_name)" ;
		$search_for = strtoupper( $search_for ) ;
		$search_for = "= '$search_for'" ;
	    }
	    $where_clause = "AND $search_by $search_for" ;
	}

	$param = $wgRequest->getText('sort');
	if( $param == "user" )
	{
	    $sort_by = "u.user_name" ;
	}
	else if( $param == "real" )
	{
	    $sort_by = "SUBSTRING_INDEX(u.user_real_name, ' ', -1), u.user_real_name" ;
	}
	else if( $param == "org" )
	{
	    $sort_by = "c.organization" ;
	}
	else if( $param == "email" )
	{
	    $sort_by = "u.user_email" ;
	}
	else
	{
	    $sort_by = "SUBSTRING_INDEX(u.user_real_name, ' ', -1), u.user_real_name" ;
	}

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;

	if( $allowed )
	{
	    $sql = "SELECT c.organization,c.address1,c.address2,c.city,c.state,c.country,c.postal_code,c.phone,c.mobile_phone,c.fax,c.supervisor_name,c.supervisor_email,c.registration_date,c.comments,c.status,u.user_id,u.user_name,u.user_real_name,u.user_email,u.user_touched FROM ".$cedar_user_table." c,".$user_table." u WHERE c.user_id=u.user_id $where_clause ORDER BY $sort_by";
	    $wgOut->addHTML( "<FORM name=\"searchform\" action=\"$wgServer/wiki/index.php/Special:CedarUserList\" method=\"POST\">\n" ) ;
	    $wgOut->addHTML( "  <TABLE WIDTH=\"200px\" BORDER=\"0\">\n" ) ;
	    $wgOut->addHTML( "    <TR>\n" ) ;
	    $wgOut->addHTML( "      <TD WIDTH=\"80\">\n" ) ;
	    $wgOut->addHTML( "        search for: <INPUT TYPE=\"text\" NAME=\"search_for\" SIZE=\"20\">" ) ;
	    $wgOut->addHTML( "      </TD>\n" ) ;
	    $wgOut->addHTML( "      <TD WIDTH=\"80\">\n" ) ;
	    $wgOut->addHTML( "        <SELECT NAME=\"search_by\" SIZE=\"1\">" ) ;
	    $wgOut->addHTML( "        <OPTION selected value=\"username\">username</OPTION>" ) ;
	    $wgOut->addHTML( "        <OPTION value=\"realname\">real name</OPTION>" ) ;
	    $wgOut->addHTML( "        <OPTION value=\"org\">organization</OPTION>" ) ;
	    $wgOut->addHTML( "        <OPTION value=\"email\">email</OPTION>" ) ;
	    $wgOut->addHTML( "        <OPTION value=\"status\">status</OPTION>" ) ;
	    $wgOut->addHTML( "        </SELECT>" ) ;
	    $wgOut->addHTML( "      </TD>\n" ) ;
	    $wgOut->addHTML( "      <TD WIDTH=\"40\">\n" ) ;
	    $wgOut->addHTML( "        <INPUT TYPE=\"SUBMIT\" NAME=\"Search\" VALUE=\"Search\">" ) ;
	    $wgOut->addHTML( "      </TD>\n" ) ;
	    $wgOut->addHTML( "    </TR>\n" ) ;
	    $wgOut->addHTML( "    <TR>\n" ) ;
	    $wgOut->addHTML( "      <TD COLSPAN=\"3\" WIDTH=\"100%\">\n" ) ;
	    $wgOut->addHTML( "        Click 'Search' with empty 'search for' to list all users\n" ) ;
	    $wgOut->addHTML( "      </TD>\n" ) ;
	    $wgOut->addHTML( "    </TR>\n" ) ;
	    $wgOut->addHTML( "  </TABLE>\n" ) ;
	    $wgOut->addHTML( "</FORM>\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	}
	else
	{
	    $wgOut->addHTML( "<BR /><BR />" ) ;
	    $sql = "SELECT c.organization,c.address1,c.address2,c.city,c.state,c.country,c.postal_code,c.phone,c.mobile_phone,c.fax,c.supervisor_name,c.supervisor_email,c.registration_date,c.comments,c.status,u.user_id,u.user_name,u.user_real_name,u.user_email,u.user_touched FROM ".$cedar_user_table." c,".$user_table." u WHERE c.user_id=".$wgUser->getID()." AND c.user_id=u.user_id";
	}
	$res = $dbw->query( $sql ) ;
	if( !$res )
	{
	    $wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />" ) ;
	}
	else
	{
	    if( $allowed )
	    {
		$num_rows = $dbw->numRows( $res ) ;
		$wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:11pt;\">Number of CEDAR users: $num_rows</SPAN><BR /><BR />\n" ) ;
	    }

	    $wgOut->addHTML( "<TABLE ALIGN=\"LEFT\" BORDER=\"1\" WIDTH=\"100%\" CELLPADDING=\"5\" CELLSPACING=\"0\">\n" ) ;
	    $wgOut->addHTML( "	<TR style=\"background-color:gainsboro;\">\n" ) ;
	    $wgOut->addHTML( "    <TD WIDTH=\"5%\" ALIGN=\"CENTER\">\n" ) ;
	    $wgOut->addHTML( "      <SPAN STYLE=\"font-weight:bold;font-size:11pt;\">#</SPAN>" ) ;
	    $wgOut->addHTML( "    </TD>\n" ) ;
	    $wgOut->addHTML( "    <TD WIDTH=\"12%\" ALIGN=\"CENTER\">\n" ) ;
	    $wgOut->addHTML( "      <SPAN STYLE=\"font-weight:bold;font-size:11pt;\"><A HREF='$wgServer/wiki/index.php/Special:CedarUserList?sort=user'>username</A></SPAN>" ) ;
	    $wgOut->addHTML( "      <BR />" ) ;
	    $wgOut->addHTML( "      <SPAN STYLE=\"font-weight:bold;font-size:11pt;\"><A HREF='$wgServer/wiki/index.php/Special:CedarUserList?sort=real'>Real Name</A></SPAN>" ) ;
	    $wgOut->addHTML( "    </TD>\n" ) ;
	    $wgOut->addHTML( "    <TD WIDTH=\"15%\" ALIGN=\"CENTER\">\n" ) ;
	    $wgOut->addHTML( "      <SPAN STYLE=\"font-weight:bold;font-size:11pt;\">Dates</SPAN>\n" ) ;
	    $wgOut->addHTML( "    </TD>\n" ) ;
	    $wgOut->addHTML( "    <TD WIDTH=\"17%\" ALIGN=\"CENTER\">\n" ) ;
	    $wgOut->addHTML( "      <SPAN STYLE=\"font-weight:bold;font-size:11pt;\"><A HREF='$wgServer/wiki/index.php/Special:CedarUserList?sort=org'>Organization</A></SPAN>" ) ;
	    $wgOut->addHTML( "    </TD>\n" ) ;
	    $wgOut->addHTML( "    <TD WIDTH=\"17%\" ALIGN=\"CENTER\">\n" ) ;
	    $wgOut->addHTML( "      <SPAN STYLE=\"font-weight:bold;font-size:11pt;\"><A HREF='$wgServer/wiki/index.php/Special:CedarUserList?sort=email'>Contact</A></SPAN>" ) ;
	    $wgOut->addHTML( "    </TD>\n" ) ;
	    $wgOut->addHTML( "    <TD WIDTH=\"17%\" ALIGN=\"CENTER\">\n" ) ;
	    $wgOut->addHTML( "      <SPAN STYLE=\"font-weight:bold;font-size:11pt;\">Supervisor</SPAN>\n" ) ;
	    $wgOut->addHTML( "    </TD>\n" ) ;
	    $wgOut->addHTML( "    <TD WIDTH=\"17%\" ALIGN=\"CENTER\">\n" ) ;
	    $wgOut->addHTML( "      <SPAN STYLE=\"font-weight:bold;font-size:11pt;\">Comments</SPAN>\n" ) ;
	    $wgOut->addHTML( "    </TD>\n" ) ;
	    $wgOut->addHTML( "  </TR  >\n" ) ;
	    $user_num = 1 ;
	    $rowcolor="white" ;
	    while( ( $obj = $dbw->fetchObject( $res ) ) )
	    {
		$id = $obj->user_id ;
		$name = $obj->user_name ;
		$real_name = $obj->user_real_name ;
		$email = $obj->user_email ;
		$touched = $obj->user_touched ;
		$org = $obj->organization ;
		$addr1 = $obj->address1 ;
		$addr2 = $obj->address2 ;
		$city = $obj->city ;
		$state = $obj->state ;
		$country = $obj->country ;
		$zip = $obj->postal_code ;
		$phone = $obj->phone ;
		$mphone = $obj->mobile_phone ;
		$fax = $obj->fax ;
		$sname = $obj->supervisor_name ;
		$semail = $obj->supervisor_email ;
		$reg = $obj->registration_date ;
		$comments = $obj->comments ;
		$status = $obj->status ;

		$wgOut->addHTML( "	<TR style=\"background-color:$rowcolor;\">\n" ) ;
		if( $rowcolor == "white" ) $rowcolor = "gainsboro" ;
		else $rowcolor = "white" ;
		$wgOut->addHTML( "    <TD WIDTH=\"5%\" ALIGN=\"CENTER\">\n" ) ;
		$wgOut->addHTML( "      <SPAN STYLE=\"font-weight:normal;font-size:10pt;\">$user_num</SPAN>\n" ) ;
		$wgOut->addHTML( "      <BR>\n" ) ;
		if( $status == "active" )
		{
		    $wgOut->addHTML( "      <IMG SRC='$wgServer/wiki/icons/active.jpg' ALT='active' TITLE='Active' BORDER='0'>\n" ) ;
		}
		else
		{
		    $wgOut->addHTML( "      <IMG SRC='$wgServer/wiki/icons/blocked.jpg' ALT='blocked' TITLE='Blocked' BORDER='0'>\n" ) ;
		}
		if( $allowed && $id != 0 && $id != 1 )
		{
		    $wgOut->addHTML( "      <BR>\n" ) ;
		    $wgOut->addHTML( "      <A HREF='$wgServer/wiki/index.php/Special:CedarUserList?action=edit&id=$id&name=$name'><IMG SRC='$wgServer/wiki/icons/edit.png' ALT='edit' TITLE='Edit'></A>\n" ) ;
		    $wgOut->addHTML( "      <A HREF='$wgServer/wiki/index.php/Special:CedarUserList?action=block&id=$id&name=$name'><IMG SRC='$wgServer/wiki/icons/delete.png' ALT='block' TITLE='Block'></A>\n" ) ;
		    $wgOut->addHTML( "      <A HREF='$wgServer/wiki/index.php/Special:CedarUserList?action=reset&id=$id&name=$name'><IMG SRC='$wgServer/wiki/icons/reset_password.jpg' ALT='reset password' TITLE='Reset Password'></A>\n" ) ;
		}
		$user_num++ ;
		$wgOut->addHTML( "    </TD>\n" ) ;
		$wgOut->addHTML( "    <TD WIDTH=\"12%\" ALIGN=\"LEFT\">\n" ) ;
		$wgOut->addHTML( "      <SPAN STYLE=\"font-weight:normal;font-size:10pt;\">$name<BR />$real_name</SPAN>\n" ) ;
		$wgOut->addHTML( "    </TD>\n" ) ;
		$wgOut->addHTML( "    <TD WIDTH=\"15%\" ALIGN=\"LEFT\">\n" ) ;
		$wgOut->addHTML( "      <SPAN STYLE=\"font-weight:normal;font-size:10pt;\">reg: $reg<BR />last: $touched</SPAN>\n" ) ;
		$wgOut->addHTML( "    </TD>\n" ) ;
		$wgOut->addHTML( "    <TD WIDTH=\"17%\" ALIGN=\"LEFT\">\n" ) ;
		$wgOut->addHTML( "      <SPAN STYLE=\"font-weight:normal;font-size:10pt;\">$org<BR />$addr1<BR />$addr2<BR />$city<BR />$state<BR />$country<BR />$zip</SPAN>\n" ) ;
		$wgOut->addHTML( "    </TD>\n" ) ;
		$wgOut->addHTML( "    <TD WIDTH=\"17%\" ALIGN=\"LEFT\">\n" ) ;
		$wgOut->addHTML( "      <SPAN STYLE=\"font-weight:normal;font-size:10pt;\">email: $email<BR />phone: $phone<BR />mobile: $mphone<BR />fax: $fax</SPAN>\n" ) ;
		$wgOut->addHTML( "    </TD>\n" ) ;
		$wgOut->addHTML( "    <TD WIDTH=\"17%\" ALIGN=\"LEFT\">\n" ) ;
		$wgOut->addHTML( "      <SPAN STYLE=\"font-weight:normal;font-size:10pt;\">$sname<BR />$semail</SPAN>\n" ) ;
		$wgOut->addHTML( "    </TD>\n" ) ;
		$wgOut->addHTML( "    <TD WIDTH=\"17%\" ALIGN=\"LEFT\">\n" ) ;
		$wgOut->addHTML( "      <SPAN STYLE=\"font-weight:normal;font-size:10pt;\">$comments</SPAN>\n" ) ;
		$wgOut->addHTML( "    </TD>\n" ) ;
		$wgOut->addHTML( "  </TR  >\n" ) ;
	    }
	    $wgOut->addHTML( "</TABLE>\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	}
    }

    function blockUser()
    {
	global $wgRequest, $wgOut, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;
	if( !$allowed )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">You do not have permission to block cedar users</SPAN><BR />\n" ) ;
	    return ;
	}

	$confirm = $wgRequest->getText( 'confirm' ) ;

	if( $confirm && $confirm == "no" )
	{
	    $this->user_list() ;
	    return ;
	}

	$id = $wgRequest->getInt( 'id' ) ;
	if( $id == 0 || $id == 1 )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">Can not delete users 0 or 1</SPAN><BR />\n" ) ;
	    return ;
	}
	$name = $wgRequest->getText( 'name' ) ;

	if( !$confirm )
	{
	    $wgOut->addHTML( "Are you sure you want to block the user $name?\n" ) ;
	    $wgOut->addHTML( "(<A HREF=\"$wgServer/wiki/index.php/Special:CedarUserList?action=block&confirm=yes&id=$id&name=$name\">Yes</A>" ) ;
	    $wgOut->addHTML( " | <A HREF=\"$wgServer/wiki/index.php/Special:CedarUserList?action=block&confirm=no\">No</A>)" ) ;
	    return ;
	}

	// the user has confirmed that they want to block the specified
	// user.
	// 1. block the cedar_user_info entry
	// 2. delete the group info for this entry
	// 3. block the user
	$dbw =& wfGetDB( DB_MASTER );
	$cedar_user_table = $dbw->tableName( 'cedar_user_info' );
	$user_table = $dbw->tableName( 'user' );
	$res = $dbw->query( "select u.user_id from $user_table u, $cedar_user_table c WHERE u.user_id = $id AND u.user_id = c.user_id" ) ;
	if( !$res )
	{
	    $db_error = $dbw->lastError() ;
	    $wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	if( $res->numRows() != 1 )
	{
	    $wgOut->addHTML( "<SPAN STYLE='color:red;font-size:12pt;font-weight:bold;'>Unable to block this user, user does not exist\n" ) ;
	    return ;
	}

	$block_success = $dbw->update( 'cedar_user_info', array( 'status' => 'blocked' ), array( 'user_id' => $id ) ) ;

	if( $block_success == false )
	{
	    $db_error = $dbw->lastError() ;
	    $wgOut->addHTML( "Failed to block user $name:<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	$delete_success = $dbw->delete( 'user_groups', array( 'ug_user' => $id, 'ug_group' => 'Cedar' ) ) ;
	if( $delete_success == false )
	{
	    $db_error = $dbw->lastError() ;
	    $wgOut->addHTML( "Failed to delete user Cedar group for $name:<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	$wgOut->addHTML( "<A HREF=\"$wgServer/wiki/index.php/Special:Blockip?wpBlockAddress=$name&wpBlockExpiry=infinite&wpEmailBan=true&wpBlockReason=no%20longer%20a%20cedar%20user\">Block Wiki User</A>" ) ;
    }

    private function resetPassword( )
    {
	global $wgRequest, $wgOut, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;
	if( !$allowed )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">You do not have permission to reset a users password</SPAN><BR />\n" ) ;
	    return ;
	}

	$confirm = $wgRequest->getText( 'confirm' ) ;

	if( $confirm && $confirm == "no" )
	{
	    $this->user_list() ;
	    return ;
	}

	$id = $wgRequest->getInt( 'id' ) ;
	if( $id == 0 || $id == 1 )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">Can not reset password for users 0 or 1</SPAN><BR />\n" ) ;
	    $this->user_list() ;
	    return ;
	}
	$name = $wgRequest->getText( 'name' ) ;

	$u = User::newFromId( $id ) ;
	if( !$u )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">The user $name does not exist</SPAN><BR />\n" ) ;
	    $this->user_list() ;
	    return ;
	}
	if( $u->getEmail() == '' )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">The user $name does not have an email address, cannot reset password</SPAN><BR />\n" ) ;
	    $this->user_list() ;
	    return ;
	}

	if( !$confirm )
	{
	    $wgOut->addHTML( "Are you sure you want to reset the password for user $name?\n" ) ;
	    $wgOut->addHTML( "(<A HREF=\"$wgServer/wiki/index.php/Special:CedarUserList?action=reset&confirm=yes&id=$id&name=$name\">Yes</A>" ) ;
	    $wgOut->addHTML( " | <A HREF=\"$wgServer/wiki/index.php/Special:CedarUserList?action=reset&confirm=no\">No</A>)" ) ;
	    return ;
	}

	// The admin has conrirmed that they want to reset the password
	// Get the User object for this given id
	// reset the password as in cedarcreateaccount
	// send an email with some nice text

	// Wipe the initial password and mail a temporary one
	$u->setPassword( null );
	$np = $u->randomPassword();
	$u->setNewpassword( $np, false );
	$u->saveSettings();

	$m = "A Cedar Administrator has reset your password for the CEDAR Wiki and Database. Your username is $name and your new password is $np. Please go to $wgServer/wiki and log in. You will be asked to create a new password, one that you can remember. Once you have created your new password you will be able to access data from the CEDAR database and the CEDAR Wiki";
	$t = "[CEDAR] CEDAR password reset";

	$result = $u->sendMail( $t, $m );
	if( WikiError::isError( $result ) )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">Failed to send user $name new password</SPAN><BR />\n" ) ;
	    $wgOut->addWikiText( wfMsg( 'mailerror', $result->getMessage() ) ) ;
	    $this->user_list() ;
	    return ;
	}

	// success
	$wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">Successfully sent new password to user $name</SPAN><BR />\n" ) ;
	$this->user_list() ;
    }

    private function editUser( )
    {
	global $wgRequest, $wgOut, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;
	if( !$allowed )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">You do not have permission to reset a users password</SPAN><BR />\n" ) ;
	    return ;
	}

	$id = $wgRequest->getInt( 'id' ) ;
	if( $id == 0 || $id == 1 )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">Can not reset password for users 0 or 1</SPAN><BR />\n" ) ;
	    $this->user_list() ;
	    return ;
	}
	$name = $wgRequest->getText( 'name' ) ;

	$u = User::newFromId( $id ) ;
	if( !$u )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">The user $name does not exist</SPAN><BR />\n" ) ;
	    $this->user_list() ;
	    return ;
	}

	$dbw =& wfGetDB( DB_MASTER );
	$cedar_user_table = $dbw->tableName( 'cedar_user_info' );
	$user_table = $dbw->tableName( 'user' );

	$sql = "SELECT c.organization,c.address1,c.address2,c.city,c.state,c.country,c.postal_code,c.phone,c.mobile_phone,c.fax,c.supervisor_name,c.supervisor_email,c.registration_date,c.comments,c.status,u.user_id,u.user_name,u.user_real_name,u.user_email,u.user_touched FROM ".$cedar_user_table." c,".$user_table." u WHERE c.user_id=u.user_id AND u.user_id=$id";
	$res = $dbw->query( $sql ) ;
	if( !$res )
	{
	    $wgOut->addHTML( "Unable to query the CEDAR User database<BR />" ) ;
	    return ;
	}
	$num_rows = $dbw->numRows( $res ) ;
	if( $num_rows != 1 )
	{
	    $wgOut->addHTML( "Couldn't find the user $name<BR />" ) ;
	    return ;
	}
	$obj = $dbw->fetchObject( $res ) ;
	if( !$obj )
	{
	    $wgOut->addHTML( "Failed to retrieve information for $name<BR>" ) ;
	    return ;
	}

	$username = $obj->user_name ;
	$realname = $obj->user_real_name ;
	$email = $obj->user_email ;
	$touched = $obj->user_touched ;
	$org = $obj->organization ;
	$address1 = $obj->address1 ;
	$address2 = $obj->address2 ;
	$city = $obj->city ;
	$state = $obj->state ;
	$country = $obj->country ;
	$postal_code = $obj->postal_code ;
	$phone = $obj->phone ;
	$mobile_phone = $obj->mobile_phone ;
	$fax = $obj->fax ;
	$supervisor_name = $obj->supervisor_name ;
	$supervisor_email = $obj->supervisor_email ;
	$reg = $obj->registration_date ;
	$comments = $obj->comments ;
	$status = $obj->status ;

	$wgOut->addHTML( "<FORM name=\"cedarcreate\" action=\"$wgServer/wiki/index.php/Special:CedarUserList?action=modify\" method=\"POST\">\n" ) ;
	$wgOut->addHTML( "    <INPUT type=\"hidden\" name=\"id\" value=\"$id\">\n" ) ;
	$wgOut->addHTML( "    <INPUT type=\"hidden\" name=\"username\" value=\"$username\">\n" ) ;
	$wgOut->addHTML( "    <TABLE ALIGN=\"LEFT\" BORDER=\"0\" WIDTH=\"660\" CELLPADDING=\"0\" CELLSPACING=\"0\">\n" ) ;

	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"30%\" CLASS=\"contexttext\" ALIGN=\"RIGHT\">\n" ) ;
	$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;\">Username:&nbsp;&nbsp;</SPAN>\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"70%\" CLASS=\"contexttext\">\n" ) ;
	$wgOut->addHTML( "		<INPUT DISABLED TYPE=\"text\" NAME=\"username\" VALUE=\"$username\" SIZE=\"30\">\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;

	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"30%\" CLASS=\"contexttext\" ALIGN=\"RIGHT\">\n" ) ;
	$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;\">Name:&nbsp;&nbsp;</SPAN>\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"70%\" CLASS=\"contexttext\">\n" ) ;
	$wgOut->addHTML( "		<INPUT TYPE=\"text\" NAME=\"realname\" VALUE=\"$realname\" SIZE=\"30\">\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;

	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"30%\" CLASS=\"contexttext\" ALIGN=\"RIGHT\">\n" ) ;
	$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;\">Email:&nbsp;&nbsp;</SPAN>\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"70%\" CLASS=\"contexttext\">\n" ) ;
	$wgOut->addHTML( "		<INPUT TYPE=\"text\" NAME=\"email\" value=\"$email\" SIZE=\"30\">\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;

	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD COLSPAN=\"2\" WIDTH=\"100%\">\n" ) ;
	$wgOut->addHTML( "	        &nbsp;\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;
	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"30%\" CLASS=\"contexttext\" ALIGN=\"RIGHT\">\n" ) ;
	$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;\">Organization:&nbsp;&nbsp;</SPAN>\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"70%\" CLASS=\"contexttext\">\n" ) ;
	$wgOut->addHTML( "		<INPUT TYPE=\"text\" NAME=\"org\" VALUE=\"$org\" SIZE=\"30\">\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;

	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"30%\" CLASS=\"contexttext\" ALIGN=\"RIGHT\">\n" ) ;
	$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;\">Address1:&nbsp;&nbsp;</SPAN>\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"70%\" CLASS=\"contexttext\">\n" ) ;
	$wgOut->addHTML( "		<INPUT TYPE=\"text\" NAME=\"address1\" VALUE=\"$address1\" SIZE=\"30\">\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;

	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"30%\" CLASS=\"contexttext\" ALIGN=\"RIGHT\">\n" ) ;
	$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;\">Address2:&nbsp;&nbsp;</SPAN>\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"70%\" CLASS=\"contexttext\">\n" ) ;
	$wgOut->addHTML( "		<INPUT TYPE=\"text\" NAME=\"address2\" VALUE=\"$address2\" SIZE=\"30\">\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;

	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"30%\" CLASS=\"contexttext\" ALIGN=\"RIGHT\">\n" ) ;
	$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;\">City:&nbsp;&nbsp;</SPAN>\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"70%\" CLASS=\"contexttext\">\n" ) ;
	$wgOut->addHTML( "		<INPUT TYPE=\"text\" NAME=\"city\" VALUE=\"$city\" SIZE=\"30\">\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;

	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"30%\" CLASS=\"contexttext\" ALIGN=\"RIGHT\">\n" ) ;
	$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;\">State:&nbsp;&nbsp;</SPAN>\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"70%\" CLASS=\"contexttext\">\n" ) ;
	$wgOut->addHTML( "		<INPUT TYPE=\"text\" NAME=\"state\" VALUE=\"$state\" SIZE=\"30\">\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;

	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"30%\" CLASS=\"contexttext\" ALIGN=\"RIGHT\">\n" ) ;
	$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;\">Postal Code:&nbsp;&nbsp;</SPAN>\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"70%\" CLASS=\"contexttext\">\n" ) ;
	$wgOut->addHTML( "		<INPUT TYPE=\"text\" NAME=\"postal_code\" VALUE=\"$postal_code\" SIZE=\"30\">\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;

	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"30%\" CLASS=\"contexttext\" ALIGN=\"RIGHT\">\n" ) ;
	$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;\">Country:&nbsp;&nbsp;</SPAN>\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"70%\" CLASS=\"contexttext\">\n" ) ;
	$wgOut->addHTML( "		<INPUT TYPE=\"text\" NAME=\"country\" VALUE=\"$country\" SIZE=\"30\">\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;

	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD COLSPAN=\"2\" WIDTH=\"100%\">\n" ) ;
	$wgOut->addHTML( "	        &nbsp;\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;
	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"30%\" CLASS=\"contexttext\" ALIGN=\"RIGHT\">\n" ) ;
	$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;\">Phone:&nbsp;&nbsp;</SPAN>\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"70%\" CLASS=\"contexttext\">\n" ) ;
	$wgOut->addHTML( "		<INPUT TYPE=\"text\" NAME=\"phone\" VALUE=\"$phone\" SIZE=\"30\">\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;

	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"30%\" CLASS=\"contexttext\" ALIGN=\"RIGHT\">\n" ) ;
	$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;\">Mobile Phone:&nbsp;&nbsp;</SPAN>\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"70%\" CLASS=\"contexttext\">\n" ) ;
	$wgOut->addHTML( "		<INPUT TYPE=\"text\" NAME=\"mobile_phone\" VALUE=\"$mobile_phone\" SIZE=\"30\">\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;

	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"30%\" CLASS=\"contexttext\" ALIGN=\"RIGHT\">\n" ) ;
	$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;\">Fax:&nbsp;&nbsp;</SPAN>\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"70%\" CLASS=\"contexttext\">\n" ) ;
	$wgOut->addHTML( "		<INPUT TYPE=\"text\" NAME=\"fax\" VALUE=\"$fax\" SIZE=\"30\">\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;

	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD COLSPAN=\"2\" WIDTH=\"100%\">\n" ) ;
	$wgOut->addHTML( "	        &nbsp;\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;
	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"30%\" CLASS=\"contexttext\" ALIGN=\"RIGHT\">\n" ) ;
	$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;\">Supervisor Name:&nbsp;&nbsp;<BR />(if programmer/student)&nbsp;&nbsp;</SPAN>\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"70%\" CLASS=\"contexttext\">\n" ) ;
	$wgOut->addHTML( "		<INPUT TYPE=\"text\" NAME=\"supervisor_name\" VALUE=\"$supervisor_name\" SIZE=\"30\">\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;

	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"30%\" CLASS=\"contexttext\" ALIGN=\"RIGHT\">\n" ) ;
	$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;\">Supervisor Email:&nbsp;&nbsp;<BR />(if programmer/student)&nbsp;&nbsp;</SPAN>\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"70%\" CLASS=\"contexttext\">\n" ) ;
	$wgOut->addHTML( "		<INPUT TYPE=\"text\" NAME=\"supervisor_email\" VALUE=\"$supervisor_email\" SIZE=\"30\">\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;

	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD COLSPAN=\"2\" WIDTH=\"100%\">\n" ) ;
	$wgOut->addHTML( "	        &nbsp;\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;

	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD COLSPAN=\"2\" WIDTH=\"100%\">\n" ) ;
	$wgOut->addHTML( "	        &nbsp;\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;
	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD COLSPAN=\"2\" WIDTH=\"100%\" CLASS=\"contexttext\">\n" ) ;
	$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;\">Comments</SPAN>\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;
	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD COLSPAN=\"2\" WIDTH=\"100%\" CLASS=\"contexttext\">\n" ) ;
	$wgOut->addHTML( "		<TEXTAREA NAME=\"comments\" ROWS=\"2\" COLS=\"60\">$comments</TEXTAREA>\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;

	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD COLSPAN=\"2\" WIDTH=\"100%\">\n" ) ;
	$wgOut->addHTML( "	        &nbsp;\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;

	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"30%\" CLASS=\"contexttext\" ALIGN=\"RIGHT\">\n" ) ;
	$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;\">Status:&nbsp;&nbsp;</SPAN>\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"70%\" CLASS=\"contexttext\">\n" ) ;
	$wgOut->addHTML( "	        <SELECT NAME=\"status\">\n" ) ;
	if( $status == "active" )
	    $wgOut->addHTML( "	            <OPTION selected VALUE=\"active\">Active</OPTION>\n" ) ;
	else
	    $wgOut->addHTML( "	            <OPTION VALUE=\"active\">Active</OPTION>\n" ) ;
	if( $status == "inactive" )
	    $wgOut->addHTML( "	            <OPTION selected VALUE=\"inactive\">InActive</OPTION>\n" ) ;
	else
	    $wgOut->addHTML( "	            <OPTION VALUE=\"inactive\">InActive</OPTION>\n" ) ;
	if( $status == "blocked" )
	    $wgOut->addHTML( "	            <OPTION selected VALUE=\"blocked\">Blocked</OPTION>\n" ) ;
	else
	    $wgOut->addHTML( "	            <OPTION VALUE=\"blocked\">Blocked</OPTION>\n" ) ;
	$wgOut->addHTML( "	        </SELECT>\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;

	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD COLSPAN=\"2\" WIDTH=\"100%\">\n" ) ;
	$wgOut->addHTML( "	        &nbsp;\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;

	$wgOut->addHTML( "	<TR>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"30%\" CLASS=\"contexttext\" ALIGN=\"CENTER\">\n" ) ;
	$wgOut->addHTML( "              <INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"Submit\">\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	    <TD WIDTH=\"70%\" CLASS=\"contexttext\" ALIGN=\"LEFT\">\n" ) ;
	$wgOut->addHTML( "              <INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"Cancel\">\n" ) ;
	$wgOut->addHTML( "	    </TD>\n" ) ;
	$wgOut->addHTML( "	</TR>\n" ) ;
	$wgOut->addHTML( "    </TABLE>\n" ) ;
	$wgOut->addHTML( "</FORM>\n" ) ;
    }

    private function modifyUser( )
    {
	global $wgRequest, $wgOut, $wgServer, $wgUser ;

	$submit = $wgRequest->getText( 'submit' ) ;
	if( $submit == "Cancel" )
	{
	    $this->user_list() ;
	    return ;
	}

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;
	if( !$allowed )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">You do not have permission to reset a users password</SPAN><BR />\n" ) ;
	    return ;
	}

	$id = $wgRequest->getInt( 'id' ) ;
	if( $id == 0 || $id == 1 )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">Can not reset password for users 0 or 1</SPAN><BR />\n" ) ;
	    $this->user_list() ;
	    return ;
	}

	$u = User::newFromId( $id ) ;
	if( !$u )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">The user $name does not exist</SPAN><BR />\n" ) ;
	    $this->user_list() ;
	    return ;
	}

	$dbw =& wfGetDB( DB_MASTER );
	$cedar_user_table = $dbw->tableName( 'cedar_user_info' );

	$username = $dbw->strencode( $wgRequest->getText( 'username' ) ) ;
	$realname = $dbw->strencode( $wgRequest->getText( 'realname' ) ) ;
	$email = $dbw->strencode( $wgRequest->getText( 'email' ) ) ;
	$org = $dbw->strencode( $wgRequest->getText( 'org' ) ) ;
	$address1 = $dbw->strencode( $wgRequest->getText( 'address1' ) ) ;
	$address2 = $dbw->strencode( $wgRequest->getText( 'address2' ) ) ;
	$city = $dbw->strencode( $wgRequest->getText( 'city' ) ) ;
	$state = $dbw->strencode( $wgRequest->getText( 'state' ) ) ;
	$country = $dbw->strencode( $wgRequest->getText( 'country' ) ) ;
	$postal_code = $dbw->strencode( $wgRequest->getText( 'postal_code' ) ) ;
	$phone = $dbw->strencode( $wgRequest->getText( 'phone' ) ) ;
	$mobile_phone = $dbw->strencode( $wgRequest->getText( 'mobile_phone' ) ) ;
	$fax = $dbw->strencode( $wgRequest->getText( 'fax' ) ) ;
	$supervisor_name = $dbw->strencode( $wgRequest->getText( 'supervisor_name' ) ) ;
	$supervisor_email = $dbw->strencode( $wgRequest->getText( 'supervisor_email' ) ) ;
	$comments = $dbw->strencode( $wgRequest->getText( 'comments' ) ) ;
	$status = $dbw->strencode( $wgRequest->getText( 'status' ) ) ;

	$update_success = $dbw->update( $cedar_user_table,
		array(
			'real_name' => "",
			'email' => "",
			'organization' => $org,
			'address1' => $address1,
			'address2' => $address2,
			'city' => $city,
			'state' => $state,
			'country' => $country,
			'postal_code' => $postal_code,
			'phone' => $phone,
			'mobile_phone' => $mobile_phone,
			'fax' => $fax,
			'supervisor_name' => $supervisor_name,
			'supervisor_email' => $supervisor_email,
			'comments' => $comments,
			'status' => $status
		),
		array(
			'user_id' => $id
		),
		__METHOD__
	) ;

	if( $update_success == false )
	{
	    $db_error = $dbw->lastError() ;
	    $wgOut->addHTML( "Failed to update cedar user $username<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	$user_table = $dbw->tableName( 'user' ) ;

	$update_success = $dbw->update( $user_table,
		array(
			'user_real_name' => $realname,
			'user_email' => $email
		),
		array(
			'user_id' => $id
		),
		__METHOD__
	) ;

	if( $update_success == false )
	{
	    $db_error = $dbw->lastError() ;
	    $wgOut->addHTML( "Failed to update user $username<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	$wgOut->addHTML( "User $username was updated successfully<br /><br />\n" ) ;
	$wgOut->addHTML( "Return to <a href=\"$wgServer/wiki/index.php/Special:CedarUserList\">Cedar User List<br />\n" ) ;
    }
}
?>
