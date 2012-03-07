<?
class Entities_Filter_Excerpt implements Zend_Filter_Interface
{

    public function filter($data,$wordCount=55)
    {
      $ellipsis = "";
        $sentance = explode(" ", $data);
        if (count($sentance) > $wordCount) {
            $sentance = array_splice($sentance, 0, $wordCount);
            $ellipsis = "...";
        }
        $excerpt = implode(" ", $sentance);
        return $excerpt . $ellipsis;
    }
}
?>