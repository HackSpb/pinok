<?php

function email_valid($email) {
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
	    return TRUE;
	} else {
		return 'Введен некорректный Email';
	}
}

function name_valid($name) {
	if (!preg_match("|[\\<>'\"-/]+|", $name)) {
		return TRUE;
	} else {
		return "В имени или фамилии используются недопустимые символы";
	}
}

function number_phone_valid($number_phone) {
	if (preg_match("/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{5,10}$/", $number_phone)) {
		return TRUE;
	} else {
		return "В номере телефона используются недопустимые символы";
	}
}

function city_valid($city) {
	if (!preg_match("|[\\<>'\"/]+|", $city)) {
		return TRUE;
	} else {
		return "В названии города используются недопустимые символы";
	}
}

function text_valid($text) {
	if (preg_match("/^[а-яА-ЯёЁa-zA-Z0-9\s\-\!\?\.\,\(\)]+$/u", $text)) {
		return TRUE;
	} else {
		return "В поле с текстом используются недопустимые символы";
	}
}

function password_valid($password) {
	if (preg_match("/^[a-zA-Z0-9\-\!\?\.\,\(\)]{4,50}+$/", $password)) {
		return TRUE;
	} else {
		return "В пароле используются недопустимые символы";
	}
}

function regulations_valid($regulations) {
	if ($regulations == TRUE) {
		return TRUE;
	} else {
		return "Необходимо согласиться с условиями проекта";
	}
}

function code_activation_valid($code_activation) {
	if (!preg_match("|[\\<>'\"-/]+|", $code_activation)) {
		return TRUE;
	} else {
		return "Ошибка, неправильная ссылка активации";
	}
}

function id_valid($id) {
	if (preg_match("/^[0-9]+$/", $id)) {
		return TRUE;
	} else {
		return 404;
	}
}
