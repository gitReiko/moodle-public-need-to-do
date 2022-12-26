<?php 

namespace NTD\Classes\Components\Forum\DatabaseWriter;

require_once 'getters/forum.php';

class Main 
{

    /**
     * All teachers whose work is monitored by the block
     */
    private $teachers;

    /**
     * Level on which data must be updated.
     */
    private $updateLevel;

    /**
     * Prepares data for the class.
     * 
     * @param array of all teachers whose work is monitored by the block
     */
    function __construct(array $teachers, string $updateLevel)
    {
        $this->teachers = $teachers;
        $this->updateLevel = $updateLevel;
        //$this->prepare_forum_data();

        $forums = new \NTD\Classes\Components\Forum\DatabaseWriter\Getters\Forum;

        print_r($forums->get_forums());

    }

    /**
     * Writes data related to forum into database.
     * 
     * @return void
     */
    public function write() : void 
    { 
        // Узяць усе форумы
        // Адкінуць форумы з адключанай падпіскай
        // Адсартаваць па назве курса
        // Адзначыць прымусовую падпіску

        // Узяць усе дыскусіі кожнага форума 
        // Калі прымусовая падпіска адключана
        // Вызначыць ці падпісан настаўнік на дыскусію

        // Узяць усе паведамленні кожнай дыскусіі усіх форумаў
        
        // Прагнаць усіх настаўнікаў па ўсіх форумах
        // дыскусіях
        // паведамленнях 
        // І вызначыць, ці прачытаў ён паведамленне

        // Запісаць інфармацыю ў базу дадзеных


        echo 'WORK';
    }



}
