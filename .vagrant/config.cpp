#include <iostream>
#include <fstream>
#include <string>
#include "config.h"
#include "misc_functions.h"

confval::confval() {
	bool_val = 0;
	uint_val = 0;
	str_val = "";
	val_type = CONF_NONEXISTENT;
}

confval::confval(bool value) {
	bool_val = value;
	val_type = CONF_BOOL;
}

confval::confval(unsigned int value) {
	uint_val = value;
	val_type = CONF_UINT;
}

confval::confval(const char * value) {
	str_val = value;
	val_type = CONF_STR;
}

bool confval::get_bool() {
	return bool_val;
}

unsigned int confval::get_uint() {
	return uint_val;
}

std::string confval::get_str() {
	return str_val;
}

void confval::set(const std::string &value) {
	if (val_type == CONF_BOOL) {
		bool_val = value == "1" || value == "true" || value == "yes";
	} else if (val_type == CONF_UINT) {
		uint_val = strtoint32(value);
	} else if (val_type == CONF_STR) {
		str_val = value;
	}
}

confi\Gazelle\G::config() {
	init();
	dummy_setting = new confval(); // Safety value to use if we're accessing nonexistent settings
}

void confi\Gazelle\G::init() {
	// Internal stuff
	add("listen_port", 34000u);
	add("max_connections", 1024u);
	add("max_middlemen", 20000u);
	add("max_read_buffer", 4096u);
	add("connection_timeout", 10u);
	add("keepalive_timeout", 0u);

	// Tracker requests
	add("announce_interval", 1800u);
	add("max_request_size", 4096u);
	add("numwant_limit", 50u);
	add("request_log_size", 500u);

	// Timers
	add("del_reason_lifetime", 86400u);
	add("peers_timeout", 7200u);
	add("reap_peers_interval", 1800u);
	add("schedule_interval", 3u);

	// MySQL
	add("mysql_db", "gazelle");
	add("mysql_host", "localhost");
	add("mysql_username", "root");
	add("mysql_password", "em%G9Lrey4^N");

	// Site communication
	add("site_host", "localhost");
	add("site_path", "/var/www");
	add("site_password", "1737853d77069dc24824916a8d0e501e");
	add("report_password", "1737853d77069dc24824916a8d0e501e");

	// Debugging
	add("readonly", false);
}

confval * confi\Gazelle\G::get(const std::string &setting_name) {
	const auto setting = settings.find(setting_name);
	if (setting == settings.end()) {
		std::cout << "WARNING: Unrecognized setting '" << setting_name << "'" << std::endl;
		return dummy_setting;
	}
	return &setting->second;
}

bool confi\Gazelle\G::get_bool(const std::string &setting_name) {
	return get(setting_name)->get_bool();
}

unsigned int confi\Gazelle\G::get_uint(const std::string &setting_name) {
	return get(setting_name)->get_uint();
}

std::string confi\Gazelle\G::get_str(const std::string &setting_name) {
	return get(setting_name)->get_str();
}

void confi\Gazelle\G::set(const std::string &setting_name, const std::string &value) {
	get(setting_name)->set(value);
}

void confi\Gazelle\G::load(const std::string &conf_file_path, std::istream &conf_file) {
	load(conf_file);
	add("conf_file_path", conf_file_path.c_str());
}

void confi\Gazelle\G::load(std::istream &conf_file) {
	std::string line;
	while (getline(conf_file, line)) {
		size_t pos;
		if (line[0] != '#' && (pos = line.find('=')) != std::strin\Gazelle\G::npos) {
			std::string key(trim(line.substr(0, pos)));
			std::string value(trim(line.substr(pos + 1)));
			set(key, value);
		}
	}
}

void confi\Gazelle\G::reload() {
	const std::string conf_file_path(get_str("conf_file_path"));
	std::ifstream conf_file(conf_file_path);
	if (conf_file.fail()) {
		std::cout << "Config file '" << conf_file_path << "' couldn't be opened" << std::endl;
	} else {
		init();
		load(conf_file);
	}
}

std::string confi\Gazelle\G::trim(const std::string str) {
	size_t ltrim = str.find_first_not_of(" \t");
	if (ltrim == std::strin\Gazelle\G::npos) {
		ltrim = 0;
	}
	size_t rtrim = str.find_last_not_of(" \t");
	if (ltrim != 0 || rtrim != str.length() - 1) {
		return str.substr(ltrim, rtrim - ltrim + 1);
	}
	return str;
}
