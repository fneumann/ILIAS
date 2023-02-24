<?php

/**
 * Service class for handling test questions
 * Hide the differences between classic core and plugin questiin types an the new factory based types
 *
 * @see ilAssQuestionType, ilAssQuestionTypeList
 */
class ilTestQuestions
{
    protected $classic = [
        'assSingleChoice',
        'assMultipleChoice',
        'assClozeTest',
        'assMatchingQuestion',
        'assOrderingQuestion',
        'assImagemapQuestion',
        'assTextQuestion',
        'assNumeric',
        'assTextSubset',
        'assOrderingHorizontal',
        'assFileUpload',
        'assErrorText',
        'assFormulaQuestion',
        'assKprimChoice',
        'assLongMenu'
    ];

    protected static self $instance;

    protected ilLanguage $lng;
    protected ilQuestionFactories $factories;
    protected ilQuestionPoolPlugins $plugins;

    /**
     * @todo add to DIC instead?
     */
    public static function instance()
    {
        global $DIC;
        if (!isset(self::$instance)) {

            self::$instance = new self(
                $DIC->language(),
                new ilQuestionFactories($DIC['component.factory']),
                new ilQuestionPoolPlugins($DIC['component.factory'])
            );
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct(
        ilLanguage $lng,
        ilQuestionFactories $factories,
        ilQuestionPoolPlugins $plugins
    )
    {
        $this->lng = $lng;
        $this->factories = $factories;
        $this->plugins = $plugins;
    }

    /**
     * Get the actual class name for a question type
     * New question types with factory share the same wrapper class
     */
    public function getQuestionClass(string $type_tag) : string
    {
        if ($this->factories->has($type_tag)) {
            return 'assWrappedQuestion';
        }
        return $type_tag; // classic type
    }

    /**
     * Get the actual gui class name for a question type
     * New question types with factory share the same wrapper class
     */
    public function getQuestionGUIClass(string $type) : string
    {
        if ($this->factories->has($type)) {
            return 'assWrappedQuestionGUI';
        }
        return $type . 'GUI'; // classic type
    }

    /**
     * Get the actual feedback class name for a question type
     * New question types with factory share the same wrapper class
     */
    public function getFeedbackClass(string $type) : string
    {
        if ($this->factories->has($type)) {
            return 'ilAssWrappedQuestionFeedback';
        }
        return str_replace('ass', 'ilAss', $type) . 'Feedback'; // classic type
    }

    /**
     * Get an instance of the question class for a question type
     */
    public function getQuestion(string $type) : assQuestion
    {
        if ($this->factories->has($type)) {
            $question = new assWrappedQuestion();
            return $question->init($this->factories->get($type));
        }
        else {
            $question_class = $type;
            return new $question_class();
        }
    }

    /**
     * Get an instance of the question GUI class for a question type
     */
    public function getQuestionGUI(string $type) : assQuestionGUI
    {
        if ($this->factories->has($type)) {
            $question_gui = new assWrappedQuestionGUI();
            return $question_gui->init($this->factories->get($type));
        }
        else {
            $gui_class = $type . 'GUI';
            return  new $gui_class();
        }
        return $question;
    }

    /**
     * Get the translated title of the question type
     */
    public function getTypeTranslation(string $type) : string
    {
       if (in_array($type, $this->classic)) {
           return $this->lng->txt($type);
       }
       if ($this->plugins->has($type)) {
           return $this->plugins->get($type)->getQuestionTypeTranslation();
       }
       if ($this->factories->has($type)) {
           return $this->factories->get($type)->getTypeTranslation();
       }
       return '';
    }

    /**
     * Check if a question type is active
     */
    public function isActive(string $type) : bool
    {
        return in_array($type, $this->classic)
            || $this->plugins->has($type)
            || $this->factories->has($type);
    }

    /**
     * Check if a question of a type can be imported
     */
    public function isImportable(string $type) : bool
    {
        return $this->isActive($type);
    }

    /**
     * Check if question type supports an offline use directly
     * classic core question types are handdeled in the caller separately
     */
    public function supportsOffline(string $type) : bool
    {
        return $this->factories->has($type)
            && $this->factories->get($type) instanceof ilQuestionOfflineFactory;
    }
}