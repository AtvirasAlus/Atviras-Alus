<?

class Entities_Events {

	public static function trigger($case, $o = array()) {
		switch ($case) {
			case "new_recipe_comment":
				$recipe = new Entities_Recipe($o["comment_recipe"]);
				$recipe->getProperties();
				if (isset($recipe->properties["brewer_id"])) {
					if ($recipe->properties["brewer_id"] != $o['comment_brewer']) {
						$recipe_owner = new Entities_User($recipe->properties["brewer_id"]);
						$recipe_commenter = new Entities_User($o['comment_brewer']);
						$recipe_commenter->getProperties();
						$subs = $recipe_owner->getAttributes();
						if ($subs["user_mail_comments"] == '1') {
							$recipe_owner->getProperties();
							if ($tpl = Entities_MicroTemplate::get('recipe_comment')) {
								$tpl_vars = array('recipe_id' => $o["comment_recipe"], 'from_user_name' => $recipe_commenter->properties["user_name"], 'to_user_name' => $recipe_owner->properties["user_name"], 'recipe_name' => $recipe->properties['recipe_name'], 'comment' => $o['comment_text']);
								Entities_Mail::mail(array($recipe_owner->properties["user_email"]), $tpl["template_subject"], Entities_MicroTemplate::render($tpl["template_body"], $tpl_vars));
							}
						}
					}
				}
				break;
		}
	}

}

?>