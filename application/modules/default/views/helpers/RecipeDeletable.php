<?
class Zend_View_Helper_RecipeDeletable extends Zend_View_Helper_Abstract{
		function recipeDeletable($recipe_id) {
			$db = Zend_Registry::get("db");
			
			$select = $db->select()
					->from("beer_awards")
					->where("recipe_id = ?", $recipe_id)
					->limit(1);
			if ($db->fetchRow($select) != false) return false;
			
			$select = $db->select()
					->from("beer_brew_sessions")
					->where("session_recipe = ?", $recipe_id)
					->limit(1);
			if ($db->fetchRow($select) != false) return false;
			
			$select = $db->select()
					->from("beer_competition_entries")
					->where("recipe_id = ?", $recipe_id)
					->limit(1);
			if ($db->fetchRow($select) != false) return false;
			
			$select = $db->select()
					->from("beer_images")
					->where("recipe_id = ?", $recipe_id)
					->limit(1);
			if ($db->fetchRow($select) != false) return false;
			
			$select = $db->select()
					->from("beer_recipes_comments")
					->where("comment_recipe = ?", $recipe_id)
					->limit(1);
			if ($db->fetchRow($select) != false) return false;
			
			$select = $db->select()
					->from("beer_recipes_favorites")
					->where("recipe_id = ?", $recipe_id)
					->limit(1);
			if ($db->fetchRow($select) != false) return false;
			
			$select = $db->select()
					->from("beer_votes")
					->where("recipe_id = ?", $recipe_id)
					->limit(1);
			if ($db->fetchRow($select) != false) return false;
			
			return true;
		}
}
?>
