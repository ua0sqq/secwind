var xmlHttp = createXmlHttpRequestObject();
// создает экземпляр объекта XMLHttpRequest
function createXmlHttpRequestObject()
{
// переменная для хранения ссылки на объект XMLHttpRequest
    var xmlHttp;
// эта часть кода должна работать во всех броузерах, за исключением
// IE6 и более старых его версий
    try
    {
// попытаться создать объект XMLHttpRequest
	xmlHttp = new XMLHttpRequest();
    }
    catch (e)
    {
// предполагается, что в качестве броузера используется
// IE6 или более старая его версия
	var XmlHttpVersions = new Array("MSXML2.XMLHTTP.6.0",
		"MSXML2.XMLHTTP.5.0",
		"MSXML2.XMLHTTP.4.0",
		"MSXML2.XMLHTTP.3.0",
		"MSXML2.XMLHTTP",
		"Microsoft.XMLHTTP");
	for (var i = 0; i < XmlHttpVersions.length && !xmlHttp; i++)
	{
	    try
	    {
// попытаться создать объект XMLHttpRequest
		xmlHttp = new ActiveXObject(XmlHttpVersions[i]);
	    }
	    catch (e) {
	    }
	}
    }
// вернуть созданный объект или вывести сообщение об ошибке
    if (!xmlHttp)
	alert("Ошибка создания объекта XMLHttpRequest.");
    else
	return xmlHttp;
}
// вызывается для чтения файла с сервера
function process()
{
// продолжать только если в xmlHttp не пустая ссылка
    if (xmlHttp)
    {
// попытаться установить соединение с сервером
	try
	{
	    // инициировать чтение файла с сервера
	    xmlHttp.open("GET", 'http://'+ location.host + "/pages/mail.php?act=ajax" , true);
	    xmlHttp.onreadystatechange = handleRequestStateChange;
	    xmlHttp.send(null);
	}
// вывести сообщение об ошибке в случае неудачи
	catch (e)
	{
	   setTimeout('process()',5000);
	}
    }
}
// эта функция вызывается при изменении состояния запроса HTTP
function handleRequestStateChange()
{
// когда readyState = 4, мы можем прочитать ответ сервера
    if (xmlHttp.readyState == 4)
    {
// продолжать, только если статус HTTP равен «OK»
	if (xmlHttp.status == 200)
	{
	    try
	    {
// обработать ответ, полученный от сервера
		handleServerResponse();
	    }
	    catch (e)
	    {
// вывести сообщение об ошибке
		setTimeout('process()',5000);
	    }
	}
	else
	    {
		setTimeout('process()',5000);
	    }
    }
}
// обрабатывает ответ, полученный от сервера
function handleServerResponse()
{
// прочитать сообщение, полученное от сервера
    var xmlResponse = xmlHttp.responseXML;
// предотвратить потенциально возможные ошибки в IE и Opera
    if (!xmlResponse || !xmlResponse.documentElement)
	throw("Неверная структура XML:\n" + xmlHttp.responseText);
// предотвратить потенциально возможные ошибки в Firefox
    var rootNodeName = xmlResponse.documentElement.nodeName;
    if (rootNodeName == "parsererror")
	throw("Invalid XML structure");
    xmlRoot = xmlResponse.documentElement;
    new_mail = xmlRoot.getElementsByTagName('new_mail').item(0).firstChild.data;
    if (new_mail > 0)
	{
    myMail = document.getElementById("myMail");
// вывести полученный код HTML
    myMail.innerHTML =  '<a href="http://'+ location.host +'/pages/mail.php">Почта ('+ new_mail +')</a>';
	}
setTimeout('process()',5000);
}