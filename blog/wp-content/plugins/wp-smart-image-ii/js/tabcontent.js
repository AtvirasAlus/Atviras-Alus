//** Tab Content script-  Dynamic Drive DHTML code library (http://www.dynamicdrive.com)
//** Last updated: Nov 8th, 06

var enabletabpersistence=0 
var tabcontentIDs=new Object()
function expandcontent(linkobj) {
	var ulid=linkobj.parentNode.parentNode.id 
	var ullist=document.getElementById(ulid).getElementsByTagName("li") 
	for (var i=0; i<ullist.length; i++) {
		ullist[i].className=""  
		if (typeof tabcontentIDs[ulid][i]!="undefined") 
		document.getElementById(tabcontentIDs[ulid][i]).style.display="none" 
	}
	linkobj.parentNode.className="wpsi-selected"  
	document.getElementById(linkobj.getAttribute("rel")).style.display="block" 
	saveselectedtabcontentid(ulid, linkobj.getAttribute("rel"))
}
function expandtab(tabcontentid, tabnumber) { 
	var thetab=document.getElementById(tabcontentid).getElementsByTagName("a")[tabnumber]
	if (thetab.getAttribute("rel"))
	expandcontent(thetab)
}
function savetabcontentids(ulid, relattribute) {
	if (typeof tabcontentIDs[ulid]=="undefined") 
	tabcontentIDs[ulid]=new Array()
	tabcontentIDs[ulid][tabcontentIDs[ulid].length]=relattribute
}
function saveselectedtabcontentid(ulid, selectedtabid){ 
	if (enabletabpersistence==1) 
	setCookie(ulid, selectedtabid)
}
function getullistlinkbyId(ulid, tabcontentid) { 
	var ullist=document.getElementById(ulid).getElementsByTagName("li")
	for (var i=0; i<ullist.length; i++) {
		if (ullist[i].getElementsByTagName("a")[0].getAttribute("rel")==tabcontentid) {
			return ullist[i].getElementsByTagName("a")[0]
			break
		}
	}
}
function initializetabcontent() {
	for (var i=0; i<arguments.length; i++) { 
		if (enabletabpersistence==0 && getCookie(arguments[i])!="") 
		setCookie(arguments[i], "")
	var clickedontab=getCookie(arguments[i]) 
	var ulobj=document.getElementById(arguments[i])
	var ulist=ulobj.getElementsByTagName("li") 
	for (var x=0; x<ulist.length; x++) { 
		var ulistlink=ulist[x].getElementsByTagName("a")[0]
		if (ulistlink.getAttribute("rel")) {
			savetabcontentids(arguments[i], ulistlink.getAttribute("rel")) 
				ulistlink.onclick=function() {
				expandcontent(this)
				return false
			}
		if (ulist[x].className=="wpsi-selected" && clickedontab=="") 
		expandcontent(ulistlink) 
		}
	} 
	if (clickedontab!="") { 
		var culistlink=getullistlinkbyId(arguments[i], clickedontab)
		if (typeof culistlink!="undefined") 
			expandcontent(culistlink) 
			else 
			expandcontent(ulist[0].getElementsByTagName("a")[0]) 
		}
	} 
}
function getCookie(Name){ 
	var re=new RegExp(Name+"=[^;]+", "i"); 
	if (document.cookie.match(re)) 
	return document.cookie.match(re)[0].split("=")[1] 
	return ""
}
function setCookie(name, value){
	document.cookie = name+"="+value 
}