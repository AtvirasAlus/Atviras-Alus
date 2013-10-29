<?php
/*
 * This is an example configuration for the mysql auth module.
 *
 * This SQL statements are optimized for following table structure.
 * If you use a different one you have to change them accordingly.
 * See comments of every statement for details.
 *
 * TABLE users
 *     uid   login   pass   firstname   lastname   email
 *
 * TABLE groups
 *     gid   name
 *
 * TABLE usergroup
 *     uid   gid
 *
 * To use this configuration you have to copy them to local.protected.php
 * or at least include this file in local.protected.php.
 */

/* Options to configure database access. You need to set up this
 * options carefully, otherwise you won't be able to access you
 * database.
 */
$conf['auth']['mysql']['server']   = 'localhost';
$conf['auth']['mysql']['user']     = 'atvirasalus';
$conf['auth']['mysql']['password'] = 'sxFU9S5atLC4FrXx';
$conf['auth']['mysql']['database'] = 'atvirasalus';

/* This option enables debug messages in the mysql module. It is
 * mostly usefull for system admins.
 */
$conf['auth']['mysql']['debug'] = 1;

/* Normally password encryption is done by DokuWiki (recommended) but for
 * some reasons it might be usefull to let the database do the encryption.
 * Set 'forwardClearPass' to '1' and the cleartext password is forwarded to
 * the database, otherwise the encrypted one.
 */
$conf['auth']['mysql']['forwardClearPass'] = 0;

/* Multiple table operations will be protected by locks. This array tolds
 * the module which tables to lock. If you use any aliases for table names
 * these array must also contain these aliases. Any unamed alias will cause
 * a warning during operation. See the example below.
 */
$conf['auth']['mysql']['TablesToLock']= array("doku_users", "doku_users AS u","doku_groups", "doku_groups AS g", "doku_usergroup", "doku_usergroup AS ug");

/***********************************************************************/
/*       Basic SQL statements for user authentication (required)       */
/***********************************************************************/

/* This statement is used to grant or deny access to the wiki. The result
 * should be a table with exact one line containing at least the password
 * of the user. If the result table is empty or contains more than one
 * row, access will be denied.
 *
 * The module access the password as 'pass' so a alias might be necessary.
 *
 * Following patters will be replaced:
 *   %{user}    user name
 *   %{pass}    encrypted or clear text password (depends on 'encryptPass')
 *   %{dgroup}  default group name
 */
$conf['auth']['mysql']['checkPass']   = "SELECT pass
                                         FROM doku_usergroup AS ug
                                         JOIN doku_users AS u ON u.uid=ug.uid
                                         JOIN doku_groups AS g ON g.gid=ug.gid
                                         WHERE login='%{user}'
                                         AND name='%{dgroup}'";

/* This statement should return a table with exact one row containing
 * information about one user. The field needed are:
 * 'pass'  containing the encrypted or clear text password
 * 'name'  the user's full name
 * 'mail'  the user's email address
 *
 * Keep in mind that Dokuwiki will access thise information through the
 * names listed above so aliasses might be neseccary.
 *
 * Following patters will be replaced:
 *   %{user}    user name
 */
$conf['auth']['mysql']['getUserInfo'] = "SELECT pass, fullname AS name, email AS mail
                                         FROM doku_users
                                         WHERE login='%{user}'";

/* This statement is used to get all groups a user is member of. The
 * result should be a table containing all groups the given user is
 * member of. The module access the group name as 'group' so a alias
 * might be nessecary.
 *
 * Following patters will be replaced:
 *   %{user}    user name
 */
$conf['auth']['mysql']['getGroups']   = "SELECT name as `group`
                                         FROM doku_groups g, doku_users u, doku_usergroup ug
                                         WHERE u.uid = ug.uid
                                         AND g.gid = ug.gid
                                         AND u.login='%{user}'";

/***********************************************************************/
/*      Additional minimum SQL statements to use the user manager      */
/***********************************************************************/

/* This statement should return a table containing all user login names
 * that meet certain filter criteria. The filter expressions will be added
 * case dependend by the module. At the end a sort expression will be added.
 * Important is that this list contains no double entries fo a user. Each
 * user name is only allowed once in the table.
 *
 * The login name will be accessed as 'user' to a alias might be neseccary.
 * No patterns will be replaced in this statement but following patters
 * will be replaced in the filter expressions:
 *   %{user}    in FilterLogin  user's login name
 *   %{name}    in FilterName   user's full name
 *   %{email}   in FilterEmail  user's email address
 *   %{group}   in FilterGroup  group name
 */
$conf['auth']['mysql']['getUsers']    = "SELECT DISTINCT login AS user
                                         FROM doku_users AS u
                                         LEFT JOIN doku_usergroup AS ug ON u.uid=ug.uid
                                         LEFT JOIN doku_groups AS g ON ug.gid=g.gid";
$conf['auth']['mysql']['FilterLogin'] = "login LIKE '%{user}'";
$conf['auth']['mysql']['FilterName']  = "fullname LIKE '%{name}'";
$conf['auth']['mysql']['FilterEmail'] = "email LIKE '%{email}'";
$conf['auth']['mysql']['FilterGroup'] = "name LIKE '%{group}'";
$conf['auth']['mysql']['SortOrder']   = "ORDER BY login";

/***********************************************************************/
/*   Additional SQL statements to add new users with the user manager  */
/***********************************************************************/

/* This statement should add a user to the database. Minimum information
 * to store are: login name, password, email address and full name.
 *
 * Following patterns will be replaced:
 *   %{user}    user's login name
 *   %{pass}    password (encrypted or clear text, depends on 'encryptPass')
 *   %{email}   email address
 *   %{name}    user's full name
 */
$conf['auth']['mysql']['addUser']     = "INSERT INTO doku_users
                                         (login, pass, email, fullname)
                                         VALUES ('%{user}', '%{pass}', '%{email}',
                                         SUBSTRING_INDEX('%{name}',' ', -1))";

/* This statement should add a group to the database.
 * Following patterns will be replaced:
 *   %{group}   group name
 */
$conf['auth']['mysql']['addGroup']    = "INSERT INTO doku_groups (name)
                                         VALUES ('%{group}')";

/* This statement should connect a user to a group (a user become member
 * of that group).
 * Following patterns will be replaced:
 *   %{user}    user's login name
 *   %{uid}     id of a user dataset
 *   %{group}   group name
 *   %{gid}     id of a group dataset
 */
$conf['auth']['mysql']['addUserGroup']= "INSERT INTO doku_usergroup (uid, gid)
                                         VALUES ('%{uid}', '%{gid}')";

/* This statement should remove a group fom the database.
 * Following patterns will be replaced:
 *   %{group}   group name
 *   %{gid}     id of a group dataset
 */
$conf['auth']['mysql']['delGroup']    = "DELETE FROM doku_groups
                                         WHERE gid='%{gid}'";

/* This statement should return the database index of a given user name.
 * The module will access the index with the name 'id' so a alias might be
 * necessary.
 * following patters will be replaced:
 *   %{user}    user name
 */
$conf['auth']['mysql']['getUserID']   = "SELECT uid AS id
                                         FROM doku_users
                                         WHERE login='%{user}'";

/***********************************************************************/
/*   Additional SQL statements to delete users with the user manager   */
/***********************************************************************/

/* This statement should remove a user fom the database.
 * Following patterns will be replaced:
 *   %{user}    user's login name
 *   %{uid}     id of a user dataset
 */
$conf['auth']['mysql']['delUser']     = "DELETE FROM doku_users
                                         WHERE uid='%{uid}'";

/* This statement should remove all connections from a user to any group
 * (a user quits membership of all groups).
 * Following patterns will be replaced:
 *   %{uid}     id of a user dataset
 */
$conf['auth']['mysql']['delUserRefs'] = "DELETE FROM doku_usergroup
                                         WHERE uid='%{uid}'";

/***********************************************************************/
/*   Additional SQL statements to modify users with the user manager   */
/***********************************************************************/

/* This statements should modify a user entry in the database. The
 * statements UpdateLogin, UpdatePass, UpdateEmail and UpdateName will be
 * added to updateUser on demand. Only changed parameters will be used.
 *
 * Following patterns will be replaced:
 *   %{user}    user's login name
 *   %{pass}    password (encrypted or clear text, depends on 'encryptPass')
 *   %{email}   email address
 *   %{name}    user's full name
 *   %{uid}     user id that should be updated
 */
$conf['auth']['mysql']['updateUser']  = "UPDATE doku_users SET";
$conf['auth']['mysql']['UpdateLogin'] = "login='%{user}'";
$conf['auth']['mysql']['UpdatePass']  = "pass='%{pass}'";
$conf['auth']['mysql']['UpdateEmail'] = "email='%{email}'";
$conf['auth']['mysql']['UpdateName']  = "fullname='%{name}'";
$conf['auth']['mysql']['UpdateTarget']= "WHERE uid=%{uid}";

/* This statement should remove a single connection from a user to a
 * group (a user quits membership of that group).
 *
 * Following patterns will be replaced:
 *   %{user}    user's login name
 *   %{uid}     id of a user dataset
 *   %{group}   group name
 *   %{gid}     id of a group dataset
 */
$conf['auth']['mysql']['delUserGroup']= "DELETE FROM doku_usergroup
                                         WHERE uid='%{uid}'
                                         AND gid='%{gid}'";

/* This statement should return the database index of a given group name.
 * The module will access the index with the name 'id' so a alias might
 * be necessary.
 *
 * Following patters will be replaced:
 *   %{group}   group name
 */
$conf['auth']['mysql']['getGroupID']  = "SELECT gid AS id
                                         FROM doku_groups
                                         WHERE name='%{group}'";


