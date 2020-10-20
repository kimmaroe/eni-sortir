<?php


namespace App\Import;

use App\Entity\Campus;
use App\Entity\User;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StudentCsvImporter
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var ValidatorInterface
     */
    private $validator;


    private $validationErrors = [];

    /**
     * StudentCsvImporter constructor.
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder, ValidatorInterface $validator)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->validator = $validator;
    }

    public function import(File $file, Campus $campus): array
    {
        $users = [];
        //ouvre le fichier en mode lecture
        $handle = fopen($file->getPathname(), 'r');
        $rowIndex = 0;
        //boucle sur les lignes une par une
        while($row = fgetcsv($handle)){
            $rowIndex++;
            //on ignore la première ligne
            if ($rowIndex === 1){ continue; }

            $user = new User();
            $user->setLastName($row[0]);
            $user->setFirstName($row[1]);
            $user->setEmail($row[2]);
            $user->setPhone($row[3]);

            $user->setCampus($campus);
            $user->setRoles(["ROLE_STUDENT"]);
            $user->setDateCreated(new \DateTime());
            $user->setIsActive(true);
            $hashedPassword = $this->passwordEncoder->encodePassword($user, 'Pa$$w0rd');
            $user->setPassword($hashedPassword);

            $errors = $this->validator->validate($user);
            if ($errors->count() > 0){
                /** @var ConstraintViolation $error */
                foreach($errors as $error){
                    $this->validationErrors[] = $error->getMessage() . " (" . $error->getInvalidValue() . ")";
                }
            }

            $users[] = $user;
        }

        return $users;
    }

    public function getErrors()
    {
        return $this->validationErrors;
    }

    public function isValid()
    {
        return count($this->validationErrors) === 0;
    }
}