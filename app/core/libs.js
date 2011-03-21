/* Epoch */

function Epoch(d,f,e,a){this.state=0;this.name=d;this.curDate=new Date();this.mode=f;this.selectMultiple=(a==true);var c=new Date();selDefMonth="";selDefYear="";if(e.value!=""){var b=(e.value||"").split("-");selDefMonth=b[1]-1;selDefYear=b[0];c.setFullYear(selDefYear,selDefMonth,b[2])}this.selectedDates=new Array(c);this.calendar;this.calHeading;this.calCells;this.rows;this.cols;this.cells=[];this.monthSelect;this.yearSelect;this.mousein=false;this.calConfig();this.setDays();this.displayYear=this.displayYearInitial;this.displayMonth=this.displayMonthInitial;this.createCalendar();if(this.mode=="popup"&&e&&e.type=="text"){this.tgt=e;this.calendar.style.position="absolute";this.topOffset=this.tgt.offsetHeight;this.leftOffset=0;this.calendar.style.top=this.getTop(e)+this.topOffset+"px";this.calendar.style.left=this.getLeft(e)+this.leftOffset+"px";document.body.appendChild(this.calendar);this.tgt.calendar=this;this.tgt.onfocus=function(){this.calendar.show()};this.tgt.onblur=function(){if(!this.calendar.mousein){this.calendar.hide()}}}else{this.container=e;this.container.appendChild(this.calendar)}this.state=2;this.visible?this.show():this.hide()}Epoch.prototype.calConfig=function(){var b=(selDefMonth==""&&selDefMonth!="0")?this.curDate.getMonth():selDefMonth;var a=(selDefYear==""&&selDefYear!="0")?this.curDate.getFullYear():selDefYear;this.displayYearInitial=a;this.displayMonthInitial=b;this.rangeYearLower=2000;this.rangeYearUpper=2030;this.minDate=new Date(2005,0,1);this.maxDate=new Date(2037,0,1);this.startDay=0;this.showWeeks=false;this.selCurMonthOnly=false;this.clearSelectedOnChange=true;switch(this.mode){case"popup":this.visible=false;break;case"flat":this.visible=true;break}this.setLang()};Epoch.prototype.setLang=function(){this.daylist=new Array("Domingo".substr(0,3),"Lunes".substr(0,3),"Martes".substr(0,3),"Miercoles".substr(0,3),"Jueves".substr(0,3),"Viernes".substr(0,3),"Sabado".substr(0,3),"Domingo".substr(0,3),"Lunes".substr(0,3),"Martes".substr(0,3),"Miercoles".substr(0,3),"Jueves".substr(0,3),"Viernes".substr(0,3),"Sabado".substr(0,3));this.months_sh=new Array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");this.monthup_title="Mes siguiente";this.monthdn_title="Mes anterior";this.clearbtn_caption="Cancelar";this.clearbtn_title="Cerrar Calendario";this.maxrange_caption="Este es el rango m�ximo"};Epoch.prototype.getTop=function(a){var b=(navigator.appVersion.indexOf("MSIE")==-1)?0:3;do{b+=a.offsetTop}while(a=a.offsetParent);return b};Epoch.prototype.getLeft=function(a){var b=0;do{b+=a.offsetLeft}while(a=a.offsetParent);return b};Epoch.prototype.show=function(){this.calendar.style.display="block";this.visible=true};Epoch.prototype.hide=function(){var b=this.tgt.value.split("-");var a=b[2]+"-"+b[1]+"-"+b[0];this.calendar.style.display="none";this.visible=false};Epoch.prototype.toggle=function(){if(this.visible){this.hide()}else{this.show()}};Epoch.prototype.setDays=function(){this.daynames=[];var a=0;for(var b=this.startDay;b<this.startDay+7;b++){this.daynames[a++]=this.daylist[b]}this.monthDayCount=new Array(31,((this.curDate.getFullYear()-2000)%4?28:29),31,30,31,30,31,31,30,31,30,31)};Epoch.prototype.setClass=function(a,b){a.setAttribute("class",b);a.setAttribute("className",b)};Epoch.prototype.createCalendar=function(){var a,b,c;this.calendar=document.createElement("table");this.calendar.setAttribute("id",this.name+"_calendar");this.setClass(this.calendar,"calendar");this.calendar.onselectstart=function(){return false};this.calendar.ondrag=function(){return false};a=document.createElement("tbody");b=document.createElement("tr");c=document.createElement("td");c.appendChild(this.createMainHeading());b.appendChild(c);a.appendChild(b);b=document.createElement("tr");c=document.createElement("td");c.appendChild(this.createDayHeading());b.appendChild(c);a.appendChild(b);b=document.createElement("tr");c=document.createElement("td");c.setAttribute("id",this.name+"_cell_td");this.calCellContainer=c;c.appendChild(this.createCalCells());b.appendChild(c);a.appendChild(b);b=document.createElement("tr");c=document.createElement("td");c.appendChild(this.createFooter());b.appendChild(c);a.appendChild(b);this.calendar.appendChild(a);this.calendar.owner=this;this.calendar.onmouseover=function(){this.owner.mousein=true};this.calendar.onmouseout=function(){this.owner.mousein=false}};Epoch.prototype.createMainHeading=function(){var a=document.createElement("div");a.setAttribute("id",this.name+"_mainheading");this.setClass(a,"mainheading");this.monthSelect=document.createElement("select");this.yearSelect=document.createElement("select");var e=document.createElement("input"),d=document.createElement("input");var c,b;for(b=0;b<12;b++){c=document.createElement("option");c.setAttribute("value",b);if(this.state==0&&this.displayMonth==b){c.setAttribute("selected","selected")}c.appendChild(document.createTextNode(this.months_sh[b]));this.monthSelect.appendChild(c)}for(b=this.rangeYearLower;b<=this.rangeYearUpper;b++){c=document.createElement("option");c.setAttribute("value",b);if(this.state==0&&this.displayYear==b){c.setAttribute("selected","selected")}c.appendChild(document.createTextNode(b));this.yearSelect.appendChild(c)}d.setAttribute("type","button");d.setAttribute("value",">");d.setAttribute("title",this.monthup_title);d.style.border="#CCCCCC solid 1px";d.style.cursor="pointer";d.style.marginLeft="2px";e.setAttribute("type","button");e.setAttribute("value","<");e.setAttribute("title",this.monthdn_title);e.style.border="#CCCCCC solid 1px";e.style.cursor="pointer";e.style.marginRight="2px";this.monthSelect.owner=this.yearSelect.owner=d.owner=e.owner=this;d.onmouseup=function(){this.owner.nextMonth()};e.onmouseup=function(){this.owner.prevMonth()};this.monthSelect.onchange=function(){this.owner.displayMonth=this.value;this.owner.displayYear=this.owner.yearSelect.value;this.owner.goToMonth(this.owner.displayYear,this.owner.displayMonth)};this.yearSelect.onchange=function(){this.owner.displayMonth=this.owner.monthSelect.value;this.owner.displayYear=this.value;this.owner.goToMonth(this.owner.displayYear,this.owner.displayMonth)};a.appendChild(e);a.appendChild(this.monthSelect);a.appendChild(this.yearSelect);a.appendChild(d);return a};Epoch.prototype.createFooter=function(){var a=document.createElement("div");var b=document.createElement("input");b.setAttribute("type","button");b.setAttribute("value",this.clearbtn_caption);b.setAttribute("title",this.clearbtn_title);b.style.border="#CCCCCC solid 1px";b.style.marginBottom='2px';b.style.cursor="pointer";b.owner=this;b.onclick=function(){this.owner.resetSelections(false)};a.appendChild(b);return a};Epoch.prototype.resetSelections=function(a){this.selectedDates=[];this.rows=new Array(false,false,false,false,false,false,false);this.cols=new Array(false,false,false,false,false,false,false);if(this.tgt){if(this.mode=="popup"){this.hide()}}if(a==true){this.goToMonth(this.displayYearInitial,this.displayMonthInitial)}else{this.reDraw()}};Epoch.prototype.createDayHeading=function(){this.calHeading=document.createElement("table");this.calHeading.setAttribute("id",this.name+"_caldayheading");this.setClass(this.calHeading,"caldayheading");var a,b,d;a=document.createElement("tbody");b=document.createElement("tr");this.cols=new Array(false,false,false,false,false,false,false);if(this.showWeeks){d=document.createElement("td");d.setAttribute("class","wkhead");d.setAttribute("className","wkhead");b.appendChild(d)}for(var c=0;c<7;c++){d=document.createElement("td");d.appendChild(document.createTextNode(this.daynames[c]));if(this.selectMultiple){d.headObj=new CalHeading(this,d,(c+this.startDay<7?c+this.startDay:c+this.startDay-7))}b.appendChild(d)}a.appendChild(b);this.calHeading.appendChild(a);return this.calHeading};Epoch.prototype.createCalCells=function(){this.rows=new Array(false,false,false,false,false,false);this.cells=[];var k=-1,c=(this.showWeeks?48:42);var d=new Date(this.displayYear,this.displayMonth,1);var g=new Date(this.displayYear,this.displayMonth,this.monthDayCount[this.displayMonth]);var a=new Date(d);a.setDate(a.getDate()+(this.startDay-d.getDay())-(this.startDay-d.getDay()>0?7:0));this.calCells=document.createElement("table");this.calCells.setAttribute("id",this.name+"_calcells");this.setClass(this.calCells,"calcells");var f,h,b;f=document.createElement("tbody");for(var e=0;e<c;e++){if(this.showWeeks){if(e%8==0){k++;h=document.createElement("tr");b=document.createElement("td");if(this.selectMultiple){b.weekObj=new WeekHeading(this,b,a.getWeek(),k)}else{b.setAttribute("class","wkhead");b.setAttribute("className","wkhead")}b.appendChild(document.createTextNode(a.getWeek()));h.appendChild(b);e++}}else{if(e%7==0){k++;h=document.createElement("tr")}}b=document.createElement("td");b.appendChild(document.createTextNode(a.getDate()));var j=new CalCell(this,b,a,k);this.cells.push(j);b.cellObj=j;a.setDate(a.getDate()+1);h.appendChild(b);f.appendChild(h)}this.calCells.appendChild(f);this.reDraw();return this.calCells};Epoch.prototype.reDraw=function(){this.state=1;var b,a;for(b=0;b<this.cells.length;b++){this.cells[b].selected=false}for(b=0;b<this.cells.length;b++){for(a=0;a<this.selectedDates.length;a++){if(this.cells[b].date.getUeDay()==this.selectedDates[a].getUeDay()){this.cells[b].selected=true}}this.cells[b].setClass()}this.state=2};Epoch.prototype.deleteCells=function(){this.calCellContainer.removeChild(this.calCellContainer.firstChild);this.cells=[]};Epoch.prototype.goToMonth=function(a,b){this.monthSelect.value=this.displayMonth=b;this.yearSelect.value=this.displayYear=a;this.deleteCells();this.calCellContainer.appendChild(this.createCalCells())};Epoch.prototype.nextMonth=function(){if(this.monthSelect.value<11){this.monthSelect.value++}else{if(this.yearSelect.value<this.rangeYearUpper){this.monthSelect.value=0;this.yearSelect.value++}else{alert(this.maxrange_caption)}}this.displayMonth=this.monthSelect.value;this.displayYear=this.yearSelect.value;this.deleteCells();this.calCellContainer.appendChild(this.createCalCells())};Epoch.prototype.prevMonth=function(){if(this.monthSelect.value>0){this.monthSelect.value--}else{if(this.yearSelect.value>this.rangeYearLower){this.monthSelect.value=11;this.yearSelect.value--}else{alert(this.maxrange_caption)}}this.displayMonth=this.monthSelect.value;this.displayYear=this.yearSelect.value;this.deleteCells();this.calCellContainer.appendChild(this.createCalCells())};Epoch.prototype.addZero=function(a){return((a<10)?"0":"")+a};Epoch.prototype.addDates=function(d,e){var a,c;for(var b=0;b<d.length;b++){c=false;for(a=0;a<this.selectedDates.length;a++){if(d[b].getUeDay()==this.selectedDates[a].getUeDay()){c=true;break}}if(!c){this.selectedDates.push(d[b])}}if(e!=false){this.reDraw()}};Epoch.prototype.removeDates=function(c,d){var a;for(var b=0;b<c.length;b++){for(a=0;a<this.selectedDates.length;a++){if(c[b].getUeDay()==this.selectedDates[a].getUeDay()){this.selectedDates.splice(a,1)}}}if(d!=false){this.reDraw()}};Epoch.prototype.outputDate=function(c,i){var j=this.addZero(c.getDate());var g=this.addZero(c.getMonth()+1);var a=this.addZero(c.getFullYear());var f=this.addZero(c.getFullYear().toString().substring(3,4));var h=(i.indexOf("yyyy")>-1?a:f);var e=this.addZero(c.getHours());var d=this.addZero(c.getMinutes());var b=this.addZero(c.getSeconds());return i.replace(/dd/g,j).replace(/mm/g,g).replace(/y{1,4}/g,h).replace(/hh/g,e).replace(/nn/g,d).replace(/ss/g,b)};Epoch.prototype.updatePos=function(a){this.calendar.style.top=this.getTop(a)+this.topOffset+"px";this.calendar.style.left=this.getLeft(a)+this.leftOffset+"px"};function CalHeading(a,c,b){this.owner=a;this.tableCell=c;this.dayOfWeek=b;this.tableCell.onclick=this.onclick}CalHeading.prototype.onclick=function(){var a=this.headObj.owner;var e=a.selectedDates;var c=a.cells;a.cols[this.headObj.dayOfWeek]=!a.cols[this.headObj.dayOfWeek];for(var d=0;d<c.length;d++){if(c[d].dayOfWeek==this.headObj.dayOfWeek&&(!a.selCurMonthOnly||c[d].date.getMonth()==a.displayMonth&&c[d].date.getFullYear()==a.displayYear)){if(a.cols[this.headObj.dayOfWeek]){if(a.selectedDates.arrayIndex(c[d].date)==-1){e.push(c[d].date)}}else{for(var b=0;b<e.length;b++){if(c[d].dayOfWeek==e[b].getDay()){e.splice(b,1);break}}}c[d].selected=a.cols[this.headObj.dayOfWeek]}}a.reDraw()};function WeekHeading(a,d,b,c){this.owner=a;this.tableCell=d;this.week=b;this.tableRow=c;this.tableCell.setAttribute("class","wkhead");this.tableCell.setAttribute("className","wkhead");this.tableCell.onclick=this.onclick}WeekHeading.prototype.onclick=function(){var a=this.weekObj.owner;var c=a.cells;var e=a.selectedDates;var d,b;a.rows[this.weekObj.tableRow]=!a.rows[this.weekObj.tableRow];for(d=0;d<c.length;d++){if(c[d].tableRow==this.weekObj.tableRow){if(a.rows[this.weekObj.tableRow]&&(!a.selCurMonthOnly||c[d].date.getMonth()==a.displayMonth&&c[d].date.getFullYear()==a.displayYear)){if(a.selectedDates.arrayIndex(c[d].date)==-1){e.push(c[d].date)}}else{for(b=0;b<e.length;b++){if(e[b].getTime()==c[d].date.getTime()){e.splice(b,1);break}}}}}a.reDraw()};function CalCell(a,d,b,c){this.owner=a;this.tableCell=d;this.cellClass;this.selected=false;this.date=new Date(b);this.dayOfWeek=this.date.getDay();this.week=this.date.getWeek();this.tableRow=c;this.tableCell.onclick=this.onclick;this.tableCell.onmouseover=this.onmouseover;this.tableCell.onmouseout=this.onmouseout;this.setClass()}CalCell.prototype.onmouseover=function(){this.setAttribute("class",this.cellClass+" hover");this.setAttribute("className",this.cellClass+" hover")};CalCell.prototype.onmouseout=function(){this.cellObj.setClass()};CalCell.prototype.onclick=function(){var b=this.cellObj;var a=b.owner;if(!a.selCurMonthOnly||b.date.getMonth()==a.displayMonth&&b.date.getFullYear()==a.displayYear){if(a.selectMultiple==true){if(!b.selected){if(a.selectedDates.arrayIndex(b.date)==-1){a.selectedDates.push(b.date)}}else{var d=a.selectedDates;for(var c=0;c<d.length;c++){if(d[c].getUeDay()==b.date.getUeDay()){d.splice(c,1)}}}}else{a.selectedDates=new Array(b.date);if(a.tgt){if(!a.checkDate||a.checkDate()){a.tgt.value=a.selectedDates[0].dateFormat();if(a.tgt.epochHandler)a.tgt.epochHandler();if(a.mode=="popup"){a.hide()}}}}a.reDraw()}};CalCell.prototype.setClass=function(){if(this.selected){this.cellClass="cell_selected"}else{if(this.owner.displayMonth!=this.date.getMonth()){this.cellClass="notmnth"}else{if(this.date.getDay()>0&&this.date.getDay()<6){this.cellClass="wkday"}else{this.cellClass="wkend"}}}if(this.date.getFullYear()==this.owner.curDate.getFullYear()&&this.date.getMonth()==this.owner.curDate.getMonth()&&this.date.getDate()==this.owner.curDate.getDate()){this.cellClass=this.cellClass+" curdate"}this.tableCell.setAttribute("class",this.cellClass);this.tableCell.setAttribute("className",this.cellClass)};Date.prototype.getDayOfYear=function(){return parseInt((this.getTime()-new Date(this.getFullYear(),0,1).getTime())/86400000+1)};Date.prototype.getWeek=function(){return parseInt((this.getTime()-new Date(this.getFullYear(),0,1).getTime())/604800000+1)};Date.prototype.getUeDay=function(){return parseInt(Math.floor((this.getTime()-this.getTimezoneOffset()*60000)/86400000))};Date.prototype.dateFormat=function(I){if(!I){I="Y-m-d"}LZ=function(c){return(c<0||c>9?"":"0")+c};var l=new Array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre","Enero".substr(0,3),"Febrero".substr(0,3),"Marzo".substr(0,3),"Abril".substr(0,3),"Mayo".substr(0,3),"Junio".substr(0,3),"Julio".substr(0,3),"Agosto".substr(0,3),"Setiembre".substr(0,3),"Octubre".substr(0,3),"Noviembre".substr(0,3),"Diciembre".substr(0,3));var z=new Array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo".substr(0,3),"Lunes".substr(0,3),"Martes".substr(0,3),"Miercoles".substr(0,3),"Jueves".substr(0,3),"Viernes".substr(0,3),"Sabado".substr(0,3));I=I+"";var n="";var w=0;var L="";var f="";var j=this.getFullYear().toString();var g=this.getMonth()+1;var J=this.getDate();var p=this.getDay();var o=this.getHours();var B=this.getMinutes();var r=this.getSeconds();var u,v,b,t,N,e,G,F,C,q,P,o,O,i,a,D;var A={};A.Y=j.toString();A.y=j.substring(2);A.n=g;A.m=LZ(g);A.F=l[g-1];A.M=l[g+11];A.j=J;A.d=LZ(J);A.D=z[p+7];A.l=z[p];A.G=o;A.H=LZ(o);if(o==0){A.g=12}else{if(o>12){A.g=o-12}else{A.g=o}}A.h=LZ(A.g);if(o>11){A.a="pm";A.A="PM"}else{A.a="am";A.A="AM"}A.i=LZ(B);A.s=LZ(r);while(w<I.length){L=I.charAt(w);f="";while((I.charAt(w)==L)&&(w<I.length)){f+=I.charAt(w++)}if(A[f]!=null){n=n+A[f]}else{n=n+f}}return n};Array.prototype.arrayIndex=function(a,c){c=(c!=null?c:0);for(var b=c;b<this.length;b++){if(a==this[b]){return b}}return -1};


//v1.1
//Copyright 2006 Adobe Systems, Inc. All rights reserved.
function AC_AX_RunContent(){
  var ret = AC_AX_GetArgs(arguments);
  AC_Generateobj(ret.objAttrs, ret.params, ret.embedAttrs);
}

function AC_AX_GetArgs(args){
  var ret = {};
  ret.embedAttrs = {};
  ret.params = {};
  ret.objAttrs = {};
  for (var i=0; i < args.length; i=i+2){
    var currArg = args[i].toLowerCase();    

    switch (currArg){	
      case "pluginspage":
      case "type":
      case "src":
        ret.embedAttrs[args[i]] = args[i+1];
        break;
      case "data":
      case "codebase":
      case "classid":
      case "id":
      case "onafterupdate":
      case "onbeforeupdate":
      case "onblur":
      case "oncellchange":
      case "onclick":
      case "ondblClick":
      case "ondrag":
      case "ondragend":
      case "ondragenter":
      case "ondragleave":
      case "ondragover":
      case "ondrop":
      case "onfinish":
      case "onfocus":
      case "onhelp":
      case "onmousedown":
      case "onmouseup":
      case "onmouseover":
      case "onmousemove":
      case "onmouseout":
      case "onkeypress":
      case "onkeydown":
      case "onkeyup":
      case "onload":
      case "onlosecapture":
      case "onpropertychange":
      case "onreadystatechange":
      case "onrowsdelete":
      case "onrowenter":
      case "onrowexit":
      case "onrowsinserted":
      case "onstart":
      case "onscroll":
      case "onbeforeeditfocus":
      case "onactivate":
      case "onbeforedeactivate":
      case "ondeactivate":
        ret.objAttrs[args[i]] = args[i+1];
        break;
      case "width":
      case "height":
      case "align":
      case "vspace": 
      case "hspace":
      case "class":
      case "title":
      case "accesskey":
      case "name":
      case "tabindex":
        ret.embedAttrs[args[i]] = ret.objAttrs[args[i]] = args[i+1];
        break;
      default:
        ret.embedAttrs[args[i]] = ret.params[args[i]] = args[i+1];
    }
  }
  return ret;
}


//v1.0
//Copyright 2006 Adobe Systems, Inc. All rights reserved.
function AC_AddExtension(src, ext)
{
  if (src.indexOf('?') != -1)
    return src.replace(/\?/, ext+'?'); 
  else
    return src + ext;
}

function AC_Generateobj(objAttrs, params, embedAttrs) 
{ 
  if( arguments.callee.tempObj ) document.body.removeChild( arguments.callee.tempObj );
  var str = "<object ";
  for (var i in objAttrs) str += i + '="' + objAttrs[i] + '" ';
  str += '>';
  for (var i in params) str += '<param name="' + i + '" value="' + params[i] + '" /> ';
  str += '<embed ';
  for (var i in embedAttrs) str += i + '="' + embedAttrs[i] + '" ';
  str += ' ></embed></object>';
  var box = arguments.callee.tempObj = document.createElement('DIV');
  document.body.appendChild( box );
  box.innerHTML = str;
  
}

function AC_FL_RunContent(){
  var ret = 
    AC_GetArgs
    (  arguments, ".swf", "movie", "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
     , "application/x-shockwave-flash"
    );
  AC_Generateobj(ret.objAttrs, ret.params, ret.embedAttrs);
}

function AC_SW_RunContent(){
  var ret = 
    AC_GetArgs
    (  arguments, ".dcr", "src", "clsid:166B1BCA-3F9C-11CF-8075-444553540000"
     , null
    );
  AC_Generateobj(ret.objAttrs, ret.params, ret.embedAttrs);
}

function AC_GetArgs(args, ext, srcParamName, classid, mimeType){
  var ret = {};
  ret.embedAttrs = {};
  ret.params = {};
  ret.objAttrs = {};
  for (var i=0; i < args.length; i=i+2){
    var currArg = args[i].toLowerCase();    

    switch (currArg){	
      case "classid":
        break;
      case "pluginspage":
        ret.embedAttrs[args[i]] = args[i+1];
        break;
      case "src":
      case "movie":	
        args[i+1] = AC_AddExtension(args[i+1], ext);
        ret.embedAttrs["src"] = args[i+1];
        ret.params[srcParamName] = args[i+1];
        break;
      case "onafterupdate":
      case "onbeforeupdate":
      case "onblur":
      case "oncellchange":
      case "onclick":
      case "ondblClick":
      case "ondrag":
      case "ondragend":
      case "ondragenter":
      case "ondragleave":
      case "ondragover":
      case "ondrop":
      case "onfinish":
      case "onfocus":
      case "onhelp":
      case "onmousedown":
      case "onmouseup":
      case "onmouseover":
      case "onmousemove":
      case "onmouseout":
      case "onkeypress":
      case "onkeydown":
      case "onkeyup":
      case "onload":
      case "onlosecapture":
      case "onpropertychange":
      case "onreadystatechange":
      case "onrowsdelete":
      case "onrowenter":
      case "onrowexit":
      case "onrowsinserted":
      case "onstart":
      case "onscroll":
      case "onbeforeeditfocus":
      case "onactivate":
      case "onbeforedeactivate":
      case "ondeactivate":
      case "type":
      case "codebase":
        ret.objAttrs[args[i]] = args[i+1];
        break;
      case "width":
      case "height":
      case "align":
      case "vspace": 
      case "hspace":
      case "class":
      case "title":
      case "accesskey":
      case "name":
      case "id":
      case "tabindex":
        ret.embedAttrs[args[i]] = ret.objAttrs[args[i]] = args[i+1];
        break;
      default:
        ret.embedAttrs[args[i]] = ret.params[args[i]] = args[i+1];
    }
  }
  ret.objAttrs["classid"] = classid;
  if (mimeType) ret.embedAttrs["type"] = mimeType;
  return ret;
}




Element.Events.enter = {
    base: 'keyup',
    condition: function(e){ return e.key == 'enter'; }
};
Element.Events.escape = {
    base: 'keyup',
    condition: function(e){ return e.key == 'esc'; }
};



/*************************************************************************************************/
/************************************** P R O T O T Y P E S **************************************/
/*************************************************************************************************/

String.prototype.ltrim = function(){ return this.replace(/^\s+/,''); };
String.prototype.rtrim = function(){ return this.replace(/\s+$/,''); };
String.prototype.trim = function(){ return this.ltrim().rtrim(); };
String.prototype.trimX = function(){ return this.replace(/\s+/g,' ').trim(); };
String.prototype.toCaps = function(){ return this.replace(/(^|\s)([a-z])/g,function(m,p1,p2){
	return p1+p2.toUpperCase();});
};
String.prototype.fill = function( i , s , r ){	/* times, fillStr, reverse */
	if( i<0 ){ r = true; i = Math.abs(i); };
	if( i > this.length ) var a=(new Array(i-this.length+1)).join( s||0 );
	else return ( r ) ? this.substr(this.length-i--): this.substr( 0 , i );
	return r ? a+this.toString() : this.toString()+a;
};
if( typeof(HTMLElement) != 'undefined' && HTMLElement.prototype && !HTMLElement.prototype.click ){
	HTMLElement.prototype.click = function(){
		var evt = this.ownerDocument.createEvent('MouseEvents');
		evt.initMouseEvent(
			'click', true, true, this.ownerDocument.defaultView, 1, 0, 0, 0, 0,
			false, false, false, false, 0, null
		);
		this.dispatchEvent( evt );
	};
};

function test( x ){
	if( typeof(x) == 'undefined' ) var op = 'undefined';
	else if( typeof(x) == 'string' ) var op = x;
	else if( typeof(x) == 'array' ) op = '[' + x.join(', ') + ']';
	else if( typeof(x) == 'object' ) op = obj2Str( x );
	else if( x.toString ) op = x.toString();
	else op = x;
	alert( op );
	function obj2Str( x ){
		var y = '{\n';
		for( var z in x ) if( x.hasOwnProperty(z) ) y += z + ': ' + x[z] + ',\n';
		return y + '}';
	};
};

function newSID(){
	return (new Date()).getMilliseconds() + Math.random();
};