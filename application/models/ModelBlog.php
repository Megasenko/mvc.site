<?php

class ModelBlog extends Model
{
    public function getPosts()
    {
        $data = null;
        try {
            $data = $this->connect()->query("SELECT * FROM  posts");

        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
        if ($data) {
            $result = [];
            while ($result[] = mysqli_fetch_assoc($data)) {
            }

            return $result;
        }

        return $data;
    }

    public function getContentOneNews($url)
    {
        $result_one = null;
        try {
            $data = $this->connect()->query("SELECT * FROM  posts WHERE url='$url'");
            if ($data) {
                $result_one = mysqli_fetch_assoc($data);
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }

        return $result_one;
    }
}