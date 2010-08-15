<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
set_time_limit(0);
echo("   __            _           _   \r\n");
echo("  / _|          | |         | |  \r\n");
echo(" | |_ ___ __  __| |__   ___ | |_ \r\n");
echo(" |  _/ _ \\\\ \\/ /| '_ \\ / _ \\| __|\r\n");
echo(" | || (_) |>  < | |_) | (_) | |_ \r\n");
echo(" |_| \___//_/\_\|_.__/ \___/ \__|\r\n");
echo("fox php bot - flatfile version!\r\n");
if ($config->debug) { echo("Debug:\r\n"); }

require_once("flatfile.php");
$db = new Flatfile();
$db->datadir = "db/";

	class fox {
		var $socket;
		var $ex = array();

		function __construct()
		{	
			$this->connect();
			$this->b = "\2";
			$this->o = "\2";
			$this->main();
		}
		function main()
		{
			global $db;
			global $config;
			$this->ownerhost = $config->ownerhost;
			while (true)
			{
				$this->date = date("n j Y g i s a");
				if (!$this->socket) {
					die("server failed\n");
				}

				$data = fgets($this->socket, 4096);
				if ($config->debug) { echo("[~R] ".$data); }
				flush();
				$this->ex = explode(' ', $data);

				foreach ($this->ex as &$trim)
				{
					$trim = trim($trim);
				}

				if ($this->ex[0] == 'PING')
				{
					$this->send_data('PONG', $this->ex[1]);
				}

				if ($this->ex[1] == '376' || $this->ex[1] == '422')
				{
					$this->idandjoin();
				}
				// PRIVMSG ~~~~~~~~~~~~
				if ($this->ex[0] == 'ERROR') {
					if (!$this->restart) {
						sleep(5); $this->connect();
					}
				}
					if ($this->ex[1] == 'PRIVMSG') {

					preg_match("/^:(.*?)!(.*?)@(.*?)[\s](.*?)[\s](.*?)[\s]:(.*?)$/", $data, $rawdata);
					$this->nick[$data] = $rawdata[1];
					$this->ident[$data] = $rawdata[2];
					$this->host[$data] = $rawdata[3];
					$this->h[$this->nick[$data]] = $this->host[$data];
					# this is messy and wastes resources, yet necessary
					$this->channel[$data] = $rawdata[5]; 
					$this->args[$data] = trim($rawdata[6]);
	if (!$this->ignore[$this->h[$this->nick[$data]]]) {
					if (strtolower($this->ex[3]) == ":.ignore") {
						if ($this->ownerhost[strtolower($this->host[$data])]) {
							if ($this->ex[4]) {
								if ($this->h[$this->ex[4]]) {
									$this->ignore[$this->h[$this->ex[4]]] = true;
									$this->privmsg($this->channel[$data], $this->b."*!*@".$this->h[$this->ex[4]].$this->o." was added to the ignore list.");
								} else { $this->privmsg($this->channel[$data], "I do not recognize ".$this->b.$this->ex[4].$this->o."."); }			
							} else { $this->notice($this->nick[$data], "Incorrect syntax. ".$this->b.".ignore <nick>"); }			
						} else { $this->notice($this->nick[$data], "You are not a fox admin."); }					
					}	
					elseif (strtolower($this->ex[3]) == ":.unignore") {
						if ($this->ownerhost[strtolower($this->host[$data])]) {
							if ($this->ex[4]) {
								if ($this->h[$this->ex[4]]) {
									unset($this->ignore[$this->h[$this->ex[4]]]);
									$this->privmsg($this->channel[$data], $this->b."*!*@".$this->h[$this->ex[4]].$this->o." was removed from the ignore list.");
								} else { $this->privmsg($this->channel[$data], "I do not recognize ".$this->b.$this->ex[4].$this->o."."); }			
							} else { $this->notice($this->nick[$data], "Incorrect syntax. ".$this->b.".ignore <nick>"); }			
						} else { $this->notice($this->nick[$data], "You are not a fox admin."); }					
					}	
					elseif (strtolower($this->ex[3]) == ":.assign") {
						if ($this->ownerhost[strtolower($this->host[$data])]) {
							if ($this->ex[4]) {
								$this->assign(trim($this->ex[4]), $this->channel[$data], $this->nick[$data]);
							} else { $this->notice($this->nick[$data], "Incorrect syntax. ".$this->b.".assign <channel>"); }	
						}
						else { $this->notice($this->nick[$data], "You are not a fox admin."); }	
					}
					elseif (strtolower($this->ex[3]) == ":.unassign") {
						if ($this->ownerhost[strtolower($this->host[$data])]) {
							if ($this->ex[4]) {
								$this->unassign(trim($this->ex[4]), $this->channel[$data], $this->nick[$data]);
							} else { $this->notice($this->nick[$data], "Incorrect syntax. ".$this->b.".unassign <channel>"); }	
						}
						else { $this->notice($this->nick[$data], "You are not a fox admin."); }	
					}
					elseif (strtolower($this->ex[3]) == ":.clear") {
						if ($this->ownerhost[strtolower($this->host[$data])]) {
							if (strtolower($this->ex[4]) == "quotes") {
								mysql_query("delete from quotes");
								$this->privmsg($this->channel[$data], "[\2Clear\2] All quotes have been deleted.");
							} elseif (strtolower($this->ex[4]) == "pics") {
								mysql_query("delete from pics");
								$this->privmsg($this->channel[$data], "[\2Clear\2] All pictures have been deleted.");
							} elseif (strtolower($this->ex[4]) == "vars") {
								unset($db);
								unset($this->h);
								$db = new Flatfile();
								$db->datadir = "db/";
								$this->usg = $this->b_convert(memory_get_usage());
								$this->rusg = $this->b_convert(memory_get_usage(true));
								$this->privmsg($this->channel[$data], "[\2Clear\2] All unnecessary variables have been unset. Memory usage is now ".$this->usg." of allocated ".$this->rusg);
								$this->clear();
								unset($this->usg); unset($this->rusg);
							} else {
								$this->privmsg($this->channel[$data], "Incorrect syntax. ".$this->b.".clear quotes|pics");
							}
							
						} else { $this->notice($this->nick[$data], "You are not a fox admin."); }					
					}
					elseif (strtolower($this->ex[3]) == ":.eval") {
						if ($this->ownerhost[strtolower($this->host[$data])]) {
							$eval = substr($this->args[$data], 6);
							$this->notice($this->nick[$data], $this->b."[Eval] ".$this->o.$eval);
							eval($eval);
						}
					}
					elseif (strtolower($this->ex[3]) == ":.help") {
						$this->privmsg($this->channel[$data], ".add <command> <response>".$this->b." | ".$this->o.".del <command>".$this->b." | ".$this->o.".info <command>".$this->b." | ".$this->o.".amnt".$this->b." | ".$this->o.".addme <command> <response>".$this->b." | ".$this->o.".addact <command> <response>".$this->b." | ".$this->o.".infoact <command>".$this->b." | ".$this->o.".addactme <command> <response>".$this->b." | ".$this->o.".delact <command>".$this->b." | ".$this->o.".q search|add|del|<id>"); $this->privmsg($this->channel[$data], ".pic add|del|<id>".$this->b." | ".$this->o.".eval <eval>".$this->b." | ".$this->o.".ignore <nick>".$this->b." | ".$this->o.".unignore <nick>".$this->b." | ".$this->o.".clear pics|quotes".$this->b." | ".$this->o.".assign <channel>".$this->b." | ".$this->o.".unassign <channel>".$this->b." | ".$this->o.".addwild <command> <response>".$this->b." | ".$this->o.".addwildme <command> <response> ".$this->b."|".$this->o." .restart"); $this->privmsg($this->channel[$data], "fox-ff (fox flatfile) version 1 - .delact, .addact, .infoact are incomplete (the former method fails)");
					}
					elseif (strtolower($this->ex[3]) == ":.invite") {
						if (isset($this->ex[4])) {
							$this->send_data("INVITE ".$this->ex[4]." ".$this->channel[$data]);
						} else { $this->privmsg($this->channel[$data], "Incorrect syntax. ".$this->b.".invite <nick>."); }
					}
					elseif (strtolower($this->ex[3]) == ":.amnt") {
						$num = 0;
						$check2 = new AndWhereClause();
						$check2->add(new SimpleWhereClause(8,'=',$config->network,'strcasecmp'));
						$check2->add(new SimpleWhereClause(7,'=',$this->channel[$data],'strcasecmp'));
						$check2 = $db->selectWhere('commands.db',$check2);
						foreach ($check2 as $check2) {
							$num++;
						}
						$this->privmsg($this->channel[$data], "I respond to ".$this->b.$num.$this->o." commands in ".$this->b.$this->channel[$data].$this->o.".");
					}
					elseif (strtolower($this->ex[3]) == ":.add") {
						$this->command[$data] = $this->ex[4];
						$this->command[$data] = str_replace("[]", " ", $this->command[$data]);
						$check = $db->selectUnique('channels.db', 1, strtolower($this->channel[$data]));
						$check2 = new AndWhereClause();
						$check2->add(new SimpleWhereClause(8,'=',$config->network,'strcasecmp'));
						$check2->add(new SimpleWhereClause(7,'=',$this->channel[$data],'strcasecmp'));
						$check2->add(new SimpleWhereClause(1,'=',$this->command[$data],'strcasecmp'));
						$check2 = $db->selectWhere('commands.db',$check2);

							if ($check) {
							if (!$check2) {
								if (isset($this->ex[4]) && isset($this->ex[5])) {
									$this->response[$data] = substr($this->args[$data], 6+strlen($this->ex[4]));
									$this->privmsg($this->channel[$data], "If someone says \"".$this->b.$this->command[$data].$this->o."\", I will now respond with \"".$this->b.$this->response[$data].$this->o."\" in ".$this->b.$this->channel[$data].$this->o.".");

									$db->insertWithAutoId('commands.db',0, array(
										0,
										1 => $this->command[$data],
										2 => $this->response[$data],
										3 => $this->nick[$data],
										4 => $this->date,
										5 => "false",
										6 => "false",
										7 => $this->channel[$data],
										8 => $config->network
										)
									);
								}
								else {
									$this->privmsg($this->channel[$data], "Incorrect syntax. ".$this->b.".add <command> <response>");
								}
							}
							else {
								$this->privmsg($this->channel[$data], "I already respond to that in ".$this->b.$this->channel[$data].$this->o.".");
							}
							}
							else {
								$this->privmsg($this->channel[$data], $this->b.$this->channel[$data].$this->o." is not in my database.");
							}
					}
					elseif (strtolower($this->ex[3]) == ":.addwild") {
						$this->command[$data] = $this->ex[4];
						$this->command[$data] = str_replace("[]", " ", $this->command[$data]);
						$check = $db->selectUnique('channels.db', 1, strtolower($this->channel[$data]));
						$check2 = new AndWhereClause();
						$check2->add(new SimpleWhereClause(8,'=',$config->network,'strcasecmp'));
						$check2->add(new SimpleWhereClause(7,'=',$this->channel[$data],'strcasecmp'));
						$check2->add(new SimpleWhereClause(1,'=',$this->command[$data],'strcasecmp'));
						$check2 = $db->selectWhere('commands.db',$check2);

							if ($check) {
							if (!$check2) {
								if (isset($this->ex[4]) && isset($this->ex[5])) {
									$this->response[$data] = substr($this->args[$data], 10+strlen($this->ex[4]));
									$this->privmsg($this->channel[$data], "If someone says \"".$this->b.$this->command[$data].$this->o."\", I will now respond with \"".$this->b.$this->response[$data].$this->o."\" in ".$this->b.$this->channel[$data].$this->o.".");
						$db->insertWithAutoId('commands.db',0, array(
										0,
										1 => $this->command[$data],
										2 => $this->response[$data],
										3 => $this->nick[$data],
										4 => $this->date,
										5 => "false",
										6 => "true",
										7 => $this->channel[$data],
										8 => $config->network
										)
									);
								}
								else {
									$this->privmsg($this->channel[$data], "Incorrect syntax. ".$this->b.".add <command> <response>");
								}
							}
							else {
								$this->privmsg($this->channel[$data], "I already respond to that in ".$this->b.$this->channel[$data].$this->o.".");
							}
							}
							else {
								$this->privmsg($this->channel[$data], $this->b.$this->channel[$data].$this->o." is not in my database.");
							}
					}
					elseif (strtolower($this->ex[3]) == ":.addwildme") {
						$this->command[$data] = $this->ex[4];
						$this->command[$data] = str_replace("[]", " ", $this->command[$data]);
						$check = $db->selectUnique('channels.db', 1, strtolower($this->channel[$data]));
						$check2 = new AndWhereClause();
						$check2->add(new SimpleWhereClause(8,'=',$config->network,'strcasecmp'));
						$check2->add(new SimpleWhereClause(7,'=',$this->channel[$data],'strcasecmp'));
						$check2->add(new SimpleWhereClause(1,'=',$this->command[$data],'strcasecmp'));
						$check2 = $db->selectWhere('commands.db',$check2);

							if ($check) {
							if (!$check2) {
								if (isset($this->ex[4]) && isset($this->ex[5])) {
									$this->response[$data] = substr($this->args[$data], 12+strlen($this->ex[4]));
									$this->privmsg($this->channel[$data], "If someone says \"".$this->b.$this->command[$data].$this->o."\" anywhere in their message, I will now respond with the action \"".$this->b.$this->response[$data].$this->o."\" in ".$this->b.$this->channel[$data].$this->o.".");

									$db->insertWithAutoId('commands.db',0, array(
										0,
										1 => $this->command[$data],
										2 => $this->response[$data],
										3 => $this->nick[$data],
										4 => $this->date,
										5 => "true",
										6 => "true",
										7 => $this->channel[$data],
										8 => $config->network
										)
									);
								}
								else {
									$this->privmsg($this->channel[$data], "Incorrect syntax. ".$this->b.".add <command> <response>");
								}
							}
							else {
								$this->privmsg($this->channel[$data], "I already respond to that in ".$this->b.$this->channel[$data].$this->o.".");
							}
							}
							else {
								$this->privmsg($this->channel[$data], $this->b.$this->channel[$data].$this->o." is not in my database.");
							}
					}
					elseif (strtolower($this->ex[3]) == ":.addme") {
						$this->command[$data] = $this->ex[4];
						$this->command[$data] = str_replace("[]", " ", $this->command[$data]);
						$check = $db->selectUnique('channels.db',1,strtolower($this->channel[$data]));
						$check2 = new AndWhereClause();
						$check2->add(new SimpleWhereClause(8,'=',$config->network,'strcasecmp'));
						$check2->add(new SimpleWhereClause(7,'=',$this->channel[$data],'strcasecmp'));
						$check2->add(new SimpleWhereClause(1,'=',$this->command[$data],'strcasecmp'));
						$check2 = $db->selectWhere('commands.db',$check2);

							if ($check) {
							if (!$check2) {
								if (isset($this->ex[4]) && isset($this->ex[5])) {

									$this->response[$data] = substr($this->args[$data], 8+strlen($this->ex[4]));
									$this->privmsg($this->channel[$data], "If someone says \"".$this->b.$this->command[$data].$this->o."\", I will now respond with the action \"".$this->b.$this->response[$data].$this->o."\" in ".$this->b.$this->channel[$data].$this->o.".");

									$db->insertWithAutoId('commands.db',0, array(
										0,
										1 => $this->command[$data],
										2 => $this->response[$data],
										3 => $this->nick[$data],
										4 => $this->date,
										5 => "true",
										6 => "false",
										7 => $this->channel[$data],
										8 => $config->network
										)
									);
								}
								else {
									$this->privmsg($this->channel[$data], "Incorrect syntax. ".$this->b.".add <command> <response>");
								}
							}
							else {
								$this->privmsg($this->channel[$data], "I already respond to that in ".$this->b.$this->channel[$data].$this->o.".");
							}
							}
							else {
								$this->privmsg($this->channel[$data], $this->b.$this->channel[$data].$this->o." is not in my database.");								$this->privmsg($this->channel[$data], $this->b.$this->channel[$data].$this->o." is not in my database.");
							}
					}
					elseif (strtolower($this->ex[3]) == ":.del") {
						$this->command[$data] = substr($this->args[$data], 5);
						$check = $db->selectUnique('channels.db', 1, strtolower($this->channel[$data]));
						$check2 = new AndWhereClause();
						$check2->add(new SimpleWhereClause(8,'=',$config->network,'strcasecmp'));
						$check2->add(new SimpleWhereClause(7,'=',$this->channel[$data],'strcasecmp'));
						$check2->add(new SimpleWhereClause(1,'=',$this->command[$data],'strcasecmp'));
						$check2 = $db->selectWhere('commands.db',$check2);
						if (isset($this->ex[4])) {
							if ($check) {
							if ($check2) {
								$this->privmsg($this->channel[$data], $this->b.$this->command[$data].$this->o." was deleted from the ".$this->b.$this->channel[$data].$this->o." command list.");
								$db->deleteWhere('commands.db', new AndWhereClause(
     								   new SimpleWhereClause(8, '=', $config->network, 'strcasecmp'),
       								   new SimpleWhereClause(7, '=', $this->channel[$data],'strcasecmp'),
       								   new SimpleWhereClause(1, '=', $this->command[$data],'strcasecmp')
								));
							}
							else {
								$this->privmsg($this->channel[$data], $this->b.$this->command[$data].$this->o." does not exist in ".$this->b.$this->channel[$data].$this->o.".");
							}
							}
							else {
								$this->privmsg($this->channel[$data], $this->b.$this->channel[$data].$this->o." is not in my database.");
							}
						}
						else {
							$this->privmsg($this->channel[$data], "Incorrect syntax. ".$this->b.".del <command>");
						}
					}
					elseif (strtolower($this->ex[3]) == ":.info") {
						$this->command[$data] = substr($this->args[$data],6);
						$check = $db->selectUnique('channels.db', 1, strtolower($this->channel[$data]));
						$check2 = new AndWhereClause();
						$check2->add(new SimpleWhereClause(8,'=',$config->network,'strcasecmp'));
						$check2->add(new SimpleWhereClause(7,'=',$this->channel[$data],'strcasecmp'));
						$check2->add(new SimpleWhereClause(1,'=',$this->command[$data],'strcasecmp'));
						$check2 = $db->selectWhere('commands.db',$check2);
						if (isset($this->ex[4])) {
							if ($check) { 
								if ($check2) {
									foreach ($check2 as $check2) {
										$this->privmsg($this->channel[$data], "Command = \"\2".$check2[1]."\2\" | Response = \"\2".$check2[2]."\2\" | Action = \2".$check2[5]."\2 | Wildcard = \2".$check2[6]."\2 | Added by \2".$check2[3]."\2 on \2".$this->convert_date($check2[4]));
									}
								} else { $this->privmsg($this->channel[$data], "\2".$this->command[$data]."\2 does not exist in \2".$this->channel[$data]."\2."); }
							} else { $this->privmsg($this->channel[$data], "\2".$this->channel[$data]."\2 is not in my database."); }
						} else { $this->privmsg($this->channel[$data], "Incorrect syntax. \2.info <command>\2"); }
					}
					elseif (strtolower($this->ex[3]) == ":.t") {
						$this->usg = $this->b_convert(memory_get_usage());
						$this->rusg = $this->b_convert(memory_get_usage(true));
						$uptime = trim(shell_exec("uptime"));
						$this->privmsg($this->channel[$data],"[\2Uptime\2] $uptime");
						$this->privmsg($this->channel[$data],"[\2Memory\2] ".$this->usg." of allocated ".$this->rusg." are being used.");
						unset($this->usg); unset($this->rusg);
					}
					elseif (strtolower($this->ex[3]) == ":.restart") {
						if ($this->ownerhost[strtolower($this->host[$data])]) {
							$file = __FILE__;
							$pid = getmypid();
							$this->restart = true;
							$this->privmsg($this->channel[$data],$this->nick[$data].': k');
							$this->send_data('QUIT :k');
							shell_exec('sleep 1; screen -dm php '.$_SERVER['PHP_SELF']);
						}
					}
					elseif (strtolower($this->ex[3]) == ":.q") {
						if (strtolower($this->ex[4]) == "add") {
							if (isset($this->ex[5])) {
								$this->quote[$data] = substr($this->args[$data], 7);
								$quotes = $db->selectAll('quotes.db');
									$quoteid = 0; foreach ($quotes as $quotes) {
										$quoteid++;
									}
								if (intval($quoteid) != 0) { $quoteid++; } else { $quoteid = 1; }
								$this->privmsg($this->channel[$data], $this->b."Added quote #".$quoteid."/".$quoteid.": ".$this->o.$this->quote[$data]);
								$quote[$data] = str_replace("", "", $quote[$data]);
								$db->insertWithAutoId('quotes.db',0,array(
										0,
										1 => $quoteid,
										2 => $this->quote[$data],
										3 => $this->channel[$data],
										4 => $this->nick[$data],
										5 => $this->date
									)
								);
							} else { $this->privmsg($this->channel[$data], "Incorrect syntax. ".$this->b.".q add <quote>"); }
						}
						elseif (strtolower($this->ex[4]) == "del") {
							if (isset($this->ex[5])) {
								if ($this->ownerhost[strtolower($this->host[$data])]) {
								$db->deleteWhere('quotes.db',new AndWhereClause(new SimpleWhereClause(1, '=', $this->ex[5],'strcasecmp'))); $this->privmsg($this->channel[$data],"[\2Delete\2] Deleted matches (if any)");		
								} else { $this->privmsg($this->channel[$data], "You are not a fox admin."); }
							} else { $this->privmsg($this->channel[$data], "Incorrect syntax. ".$this->b.".q del <quote id>"); }
						}
						elseif (strtolower($this->ex[4]) == "rand") {
							$this->quote_rand($this->channel[$data]);
						}
						elseif (strtolower($this->ex[4]) == "amnt") {
							$this->quote_amnt($this->channel[$data]);
						}
						elseif (strtolower($this->ex[4]) == "search") {
							if (isset($this->ex[5])) {
								$search = substr($data,strlen($this->ex[0])+strlen($this->ex[1])+strlen($this->ex[2])+strlen($this->ex[3])+strlen($this->ex[4])+4);
								$search = trim($search);
								$this->quote_search($this->channel[$data],$search);
								} else { $this->privmsg($this->channel[$data],'Incorrect syntax. '."\2.q search <query>\2"); }
						}
						else {
							if ($this->ex[4]) {
								$this->quote_view($this->ex[4], $this->channel[$data]);
							} else { $this->privmsg($this->channel[$data], "Incorrect syntax. ".$this->b.".q add|del|rand|amnt|search <quote>|<quote id>|<string>"); }
						}
					}
					elseif (strtolower($this->ex[3]) == ":.pic") {
						if (strtolower($this->ex[4]) == "add") {
							if (isset($this->ex[5])) {
								$this->quote[$data] = substr($this->args[$data], 9);
								$quotes = $db->selectAll('pics.db');
									$quoteid = 0; foreach ($quotes as $quotes) {
										$quoteid++;
									}
								if (intval($quoteid) != 0) { $quoteid++; } else { $quoteid = 1; }
								$this->privmsg($this->channel[$data], $this->b."Added pic #".$quoteid."/".$quoteid.": ".$this->o.$this->quote[$data]);
								$quote[$data] = str_replace("", "", $quote[$data]);
								$db->insertWithAutoId('pics.db',0,array(
										0,
										1 => $quoteid,
										2 => $this->quote[$data],
										3 => $this->channel[$data],
										4 => $this->nick[$data],
										5 => $this->date
									)
								);
							} else { $this->privmsg($this->channel[$data], "Incorrect syntax. ".$this->b.".q add <quote>"); }
						}
						elseif (strtolower($this->ex[4]) == "search") {
							if (isset($this->ex[5])) {
								$search = substr($data,strlen($this->ex[0])+strlen($this->ex[1])+strlen($this->ex[2])+strlen($this->ex[3])+strlen($this->ex[4])+4);
								$search = trim($search);
								$this->pic_search($this->channel[$data],$search);
							} else { $this->privmsg($this->channel[$data],'Incorrect syntax.'."\2.pic search <query>\2"); }
						}
						elseif (strtolower($this->ex[4]) == "del") {
							if (isset($this->ex[5])) {
								if ($this->ownerhost[strtolower($this->host[$data])]) {
									$db->deleteWhere('pics.db',new AndWhereClause(new SimpleWhereClause(1, '=', $this->ex[5],'strcasecmp'))); $this->privmsg($this->channel[$data],"[\2Delete\2] Deleted matches (if any)");					
								} else { $this->privmsg($this->channel[$data], "You are not a fox admin."); }
							} else { $this->privmsg($this->channel[$data], "Incorrect syntax. ".$this->b.".q del <quote id>"); }
						}
						elseif (strtolower($this->ex[4]) == "rand") {
							$this->pic_rand($this->channel[$data]);
						}
						elseif (strtolower($this->ex[4]) == "amnt") {
							$this->pic_amnt($this->channel[$data]);
						}
						else {
							if (isset($this->ex[4])) {
								$this->pic_view($this->ex[4], $this->channel[$data]);
							} else { $this->privmsg($this->channel[$data], "Incorrect syntax. ".$this->b.".pic search|add|del|rand|amnt <query>|<quote>|<quote id>"); }
						}
					}
					elseif (strtolower($this->ex[3]) == ":".strtolower($config->serv_nick).":") {
						if (strtolower($this->ex[4]) == "eval") {
							if ($this->ownerhost[strtolower($this->host[$data])]) {
								$eval = substr($this->args[$data],strlen($this->ex[3])+5);
								$this->privmsg($this->channel[$data],"[\2Eval\2] ".$eval);
								eval($eval);
							}
						}
						elseif (strtolower($this->ex[4]) == "print_r") {
							if ($this->ownerhost[strtolower($this->host[$data])]) {
								$this->privmsg($this->channel[$data],"[\2print_r\2] ".$this->ex[5]);
								$var = $this->ex[5];
								eval("print_r($var);");
							}
						}
						elseif (strtolower($this->ex[4]) == "say") {
							if ($this->ownerhost[strtolower($this->host[$data])]) {
								$say = substr($this->args[$data],strlen($this->ex[3])+4);
								$this->privmsg($this->channel[$data],$say);
							}
						}
						elseif (strtolower($this->ex[4]) == "tell") {
								if ($this->ownerhost[strtolower($this->host[$data])]) {
								$say = substr($this->args[$data],strlen($this->ex[3])+6+strlen($this->ex[5]));
								$this->privmsg($this->channel[$data],$this->ex[5].": ".$say);
							}
						}
					}
					else {
						$command = new AndWhereClause();
						$command->add(new SimpleWhereClause(8, '=', $config->network, 'strcasecmp'));
						$command->add(new SimpleWhereClause(7, '=', $this->channel[$data], 'strcasecmp'));
						$command->add(new SimpleWhereClause(1, '=', $this->args[$data], 'strcasecmp'));
						$command->add(new SimpleWhereClause(6, '=', "false", 'strcasecmp'));
						$command = $db->selectWhere('commands.db',$command);
						if ($command) {
							foreach ($command as $commandr) {
								$this->response[$data] = str_replace("\$nick", $this->nick[$data], $commandr[2]);
								$this->response[$data] = str_replace("\$capsnick", strtoupper($this->nick[$data]), $this->response[$data]);
								$this->response[$data] = str_replace("\$ident", $this->ident[$data], $this->response[$data]);
								$this->response[$data] = str_replace("\$host", $this->host[$data], $this->response[$data]);
								$this->response[$data] = str_replace("\$sexuality", $this->sexuality($this->nick[$data]), $this->response[$data]);
								$this->response[$data] = str_replace("\$lol", $this->lol(), $this->response[$data]);
								$this->response[$data] = str_replace("\$rand", rand(1,99), $this->response[$data]);


								if ($commandr[5] == "true") {
									$this->privmsg($this->channel[$data], "\001ACTION ".$this->response[$data]."\001");
								}
								else {
								$this->response[$data] = str_replace(" \$line ", "\nPRIVMSG ".$this->channel[$data]." :", $this->response[$data]);
								$this->privmsg($this->channel[$data], $this->response[$data]);
								}
							}
						} else { 
							$ex = explode(" ", $this->args[$data]);
							foreach ($ex as $word) { 
								$this->wild($word,$this->channel[$data], $this->nick[$data], $this->ident[$data], $this->host[$data]);
							}
		}			}
				$this->clear();
	}
				} // end privmsg
			}
		}

	// main functions

		function wild ($word, $channel, $nick, $ident, $host) {
			global $db;
			global $config;
			if ($config->debug) { echo("checking word for wildcard in $channel: $word\n"); }
			$check = new AndWhereClause();
			$check->add(new SimpleWhereClause(8, '=', $config->network, 'strcasecmp'));
			$check->add(new SimpleWhereClause(7, '=', $channel, 'strcasecmp'));
			$check->add(new SimpleWhereClause(1, '=', $word, 'strcasecmp'));
			$check->add(new SimpleWhereClause(6, '=', 'true', 'strcasecmp'));
			$cr = $db->selectWhere('commands.db',$check);
					if ($cr) {
						foreach ($cr as $cr) {
							$response = str_replace("\$nick", $nick, $cr[2]);
							$response = str_replace("\$capsnick", strtoupper($nick), $response);
							$response = str_replace("\$ident", $ident, $response);
							$response = str_replace("\$host", $host, $response);
							$response = str_replace("\$sexuality", $this->sexuality($nick), $response);
							$response = str_replace("\$lol", $this->lol(), $response);
							$response = str_replace("\$rand", rand(1,99), $response);

							if ($cr[5] == "false") {
							$response = str_replace(" \$line ", "\nPRIVMSG ".$channel." :", $response);
							$this->privmsg($channel, $response);
							} else {
							$this->privmsg($channel,"\001ACTION ".$response."\001");
							}
						}
					return true;
					} else { return false; }
		}

		function assign ($channel, $rchannel, $nick) {
			global $config;
			global $db;
			$c = new AndWhereClause();
			$c->add(new SimpleWhereClause(1, '=', $channel, 'strcasecmp'));
			$c->add(new SimpleWhereClause(4, '=', $config->network, 'strcasecmp'));
			$c = $db->selectWhere('channels.db',$c);
			$check = str_split($channel);
				if ($check[0] == "#") {
					if (!$c) {
						$this->join($channel);
						$this->privmsg($rchannel, $this->b.$channel.$this->o." has been added to my database.");
				$db->insertWithAutoId('channels.db',0, array(
						0,
						1 => strtolower($channel),
						2 => $this->date,
						3 => $nick,
						4 => $config->network
					)
				);
					} else { $this->privmsg($rchannel, $this->b.$channel.$this->o." is already in my database."); }
				} else { $this->privmsg($rchannel, $this->b.$channel.$this->o." is not a valid channel name."); }
		}

		function unassign ($channel, $rchannel, $nick) {
			global $config;
			global $db;
			$c = $db->selectUnique('channels.db',1,strtolower($channel));
			$check = str_split($channel);
				if ($check[0] == "#") {
					if ($c) {
						$this->part($channel);
						$this->privmsg($rchannel, $this->b.$channel.$this->o." has been removed from my database.");
						$s = new AndWhereClause();
						$s->add(new SimpleWhereClause(1, '=', $channel, 'strcasecmp'));
						$db->deleteWhere('channels.db',$s);
					} else { $this->privmsg($rchannel, $this->b.$channel.$this->o." is not in my database."); }
				} else { $this->privmsg($rchannel, $this->b.$channel.$this->o." is not a valid channel name."); }
		}
		function quote_search ($channel,$query) {
			global $db;
                        $quotes = $db->selectAll('quotes.db');
			$this->privmsg($channel,"All quotes containing \"\2$query\2\":");
			$i = 0;
                        foreach ($quotes as $quote) {
				if (stripos($quote[2],$query) !== false) {
					$this->quote_view($quote[1],$channel);
					usleep(500000);
					$i++;
				}
                        }
			$this->privmsg($channel,"End search \"\2$query\2\". $i quotes were found.");

		}
                function pic_search ($channel,$query) {
                        global $db;
                        $quotes = $db->selectAll('pics.db');
                        $this->privmsg($channel,"All pictures containing \"\2$query\2\":");
                        $i = 0;
                        foreach ($quotes as $quote) {
                                if (stripos($quote[2],$query) !== false) {
                                        $this->pic_view($quote[1],$channel);
                                        usleep(500000);
                                        $i++;
                                }
                        }
                        $this->privmsg($channel,"End search \"\2$query\2\". $i pictures were found.");

                }
		function quote_view ($quote, $channel) {
			global $db;
			$totalquotes = 0;
			$quotes = $db->selectAll('quotes.db');
			foreach ($quotes as $quotes) {
				$totalquotes++;
			}
			if (intval($quote) != 0) {
			$q = $db->selectUnique('quotes.db',1,$quote);
				if ($q) {
					$this->privmsg($channel, $this->b."Quote #".$quote."/".$totalquotes.": ".$this->o.$q[2]);
				}
				else { $this->privmsg($channel, "Quote ".$this->b."#".$quote.$this->o." does not exist."); }
			 } else { $this->privmsg($channel, "The quote ID must be an integer."); }
		}
		function quote_rand ($channel) {
			global $db;
			$totalquotes = 0;
			$quotes = $db->selectAll('quotes.db');
			foreach ($quotes as $quotes) {
				$totalquotes++;
			}
			$quoteid = rand(1,$totalquotes);
			$this->quote_view($quoteid,$channel);
		}
		function quote_amnt ($channel) {
			global $db;
			$totalquotes = 0;
			$quotes = $db->selectAll('quotes.db');
			foreach ($quotes as $quotes) {
				$totalquotes++;
			}
			$this->privmsg($channel, "There are ".$this->b.$totalquotes.$this->o." quotes in my database.");
		}
		function pic_view ($quote, $channel) {
			global $db;
			$totalquotes = 0;
			$quotes = $db->selectAll('pics.db');
			foreach ($quotes as $quotes) {
				$totalquotes++;
			}
			if (intval($quote) != 0) {
			$q = $db->selectUnique('pics.db',1,$quote);
				if ($q) {
					$this->privmsg($channel, $this->b."Picture #".$quote."/".$totalquotes.": ".$this->o.$q[2]);
				}
				else { $this->privmsg($channel, "Picture ".$this->b."#".$quote.$this->o." does not exist."); }
			 } else { $this->privmsg($channel, "The picture ID must be an integer."); }
		}
		function pic_rand ($channel) {
			global $db;
			$totalquotes = 0;
			$quotes = $db->selectAll('pics.db');
			foreach ($quotes as $quotes) {
				$totalquotes++;
			}
			$quoteid = rand(1,$totalquotes);
			$this->pic_view($quoteid,$channel);
		}
		function pic_amnt ($channel) {
			global $db;
			$totalquotes = 0;
			$quotes = $db->selectAll('pics.db');
			foreach ($quotes as $quotes) {
				$totalquotes++;
			}
			$this->privmsg($channel, "There are ".$this->b.$totalquotes.$this->o." pictures in my database.");
		}

		function mode ($a, $b) {
			$this->send_data("MODE ".$a." ".$b);
		}

		function join ($channel, $password) {
			$this->send_data("JOIN :".$channel." ".$password);
		}

		function quit ($msg) {
			$this->send_data("QUIT :".$msg);
		}

		function part ($channel, $msg) {
			if ($msg) {
				$this->send_data("PART ".$channel." :".$msg);
			}
			else {
				$this->send_data("PART ".$channel);
			}
		}

		function privmsg($a, $b) {
			$this->send_data("PRIVMSG ".$a." :".$b);
		}

		function notice($a, $b) {
			$this->send_data("NOTICE ".$a." :".$b);
		}

		function convert_date ($date) {
			// month, day, year, hour, minute, second, am/pm
			$d = explode(" ", $date);
			$month = $d[0];
				if (intval($month) == 1) { $month = "January"; }
				elseif (intval($month) == 2) { $month = "February"; }
				elseif (intval($month) == 3) { $month = "March"; }
				elseif (intval($month) == 4) { $month = "April"; }
				elseif (intval($month) == 5) { $month = "May"; }
				elseif (intval($month) == 6) { $month = "June"; }
				elseif (intval($month) == 7) { $month = "July"; }
				elseif (intval($month) == 8) { $month = "August"; }
				elseif (intval($month) == 9) { $month = "September"; }
				elseif (intval($month) == 10) { $month = "October"; }
				elseif (intval($month) == 11) { $month = "November"; }
				elseif (intval($month) == 12) { $month = "December"; }
				else { $month = "N/A"; }
			$date = $month." ".$d[1].", ".$d[2]." at ".$d[3].":".$d[4].":".$d[5]." ".strtoupper($d[6]).$this->o.".";
			return $date;
		}

		function idandjoin () {
			global $config;
			global $db;
				$this->send_data("PRIVMSG NickServ :IDENTIFY ".$config->serv_nickpass);
				sleep(1);
					$s = new AndWhereClause();
					$s->add(new SimpleWhereClause(4, '=', $config->network, 'strcasecmp'));
					$channels = $db->selectWhere('channels.db',$s);
					foreach ($channels as $channel) {
						$this->join($channel[1]);
					}
		}
		function clear () {
			unset($this->command);
			unset($this->quote);
			unset($this->response);
			unset($this->channel);
			unset($this->nick);
			unset($this->ident);
			unset($this->host);
			unset($this->args);
		}
		function connect () {
			global $config;
			$bindTo = '69.164.222.215';
			$server = $config->server;
			$port = $config->serv_port;
			$this->socket = fsockopen($config->server,$config->serv_port);
			$this->send_data("USER", $config->serv_ident." * * :".$config->serv_realname);
			$this->send_data("NICK", $config->serv_nick);
		}

		function send_data($cmd, $msg = null) 
		{
			global $config;
			fputs($this->socket, trim($cmd.' '.$msg)."\r\n");
			if ($config->debug) { echo("[~S] ".trim($cmd.' '.$msg)."\r\n"); }
		}
	// other functions

		function b_convert($bytes)
		{
		    $ext = array('bytes', 'kb', 'mb', 'gb', 'tb', 'pb', 'eb', 'zb', 'yb');
		    $unitCount = 0;
		    for(; $bytes >= 1024; $unitCount++) $bytes /= 1024;
		    return $bytes ." ". $ext[$unitCount];
		}

		function sexuality ($nick) {
			$s = rand(1,5);
				if (strpos($nick,"starcoder") !== false) { return "asexual"; }
				if ($s == 1) {
					return "straight";
				}
				elseif ($s == 2) {
					return "gay";
				}
				elseif ($s == 3) {
					return "lesbian";
				}
				elseif ($s == 4) {
					return "bi";
				}
				elseif ($s == 5) {
					return "asexual";
				}
		}

		function lol () {
			$s = rand(1,6);
				if ($s == 1) {
					return "hoe";
				}
				elseif ($s == 2) {
					return "hooker";
				}
				elseif ($s == 3) {
					return "whore";
				}
				elseif ($s == 4) {
					return "bitch";
				}
				elseif ($s == 5) {
					return "slut";
				}
				elseif ($s == 6) {
					return "pimp";
				}
		}

	}
	$fox = new fox();
?>
