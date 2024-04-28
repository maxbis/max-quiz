# Max-Quiz

### Use Case

In a classroom, as a teacher, I want to execute a formative assessment. I want to be able to start a quiz, minimize the change that students help each other and I want to be able to change quizzes, create new ones without much hassle.

Furthermore, I want to monitor the quiz once it is running and I want to be able to spot irregularities during the quiz session.

The commercial product Socrative matches this use case pretty well, but I kept on running into limitations. So I made my own quiz app.

### Installation

##### Clone the repo

git clone [https://github.com/maxbis/max-quiz](https://github.com/maxbis/max-quiz)

##### Update with composer

composer update

##### Database

Install the MySQL database by importing the file max-quiz-database-plus example\_data.sql, note that this files also creates the database for you. If you want to name the database different, please edit the SLQ file.

##### Create and/or set rights to some directories

Depending on the system you are running on, you might need to create a few directories, since empty directories are not copied with GitHub. Maybe you need to set the file/directory rights, but this depends on your system and your Web server settings.

The framework Yii will give pretty clear hints for this.

The Yii app can be opened by navigating to the web directory, all routes mentioned in this document are relative to this directory.

(this documentation needs to be updated)

### Students view

[![image-1704213000660.png](https://www.roc.ovh/uploads/images/gallery/2024-01/scaled-1680-/image-1704213000660.png)](https://www.roc.ovh/uploads/images/gallery/2024-01/image-1704213000660.png)

The app starts by navigation to the web directory.

If you are not logged in you get a screen to start a quiz, this is the student's view.

A student need to provide his name, class and a password to start the quiz.

### Student session

Once a student is started. He will always get the next unanswered questions. You can refresh the page, or do whatever. The sessions kept in a cookie that is valid for 2 hours. This is also the maximum time a student can work on a quiz.

When for what reason the student looses his session (he switched from device a to device b), you can provide the student a link to continue to finish the quiz.

### Admin  


Go to the login, by navigating to /admin.

The standard database has one admin account, called admin with the password admin.

The management of the users is all standard Yii: navigate to /tbl-user to manage this.

For now only username and password are used.

### Quiz Screen

[![image-1704214311510.png](https://www.roc.ovh/uploads/images/gallery/2024-01/scaled-1680-/image-1704214311510.png)](https://www.roc.ovh/uploads/images/gallery/2024-01/image-1704214311510.png)

In the quiz screen you can manage the quizzes. Only active quizzes can be started.

Once a quiz is started, it can only be forced to be stopped on a student level.

### Progress Screen

[![image-1704214756481.png](https://www.roc.ovh/uploads/images/gallery/2024-01/scaled-1680-/image-1704214756481.png)](https://www.roc.ovh/uploads/images/gallery/2024-01/image-1704214756481.png)

Here you can monitor the progress of the quiz. The questions are mixed per student.

By pressing on the progress column ("Progr."), you can edit the submission. You can change the name, class, correct the score, and force the quiz to be finished.

The code in shown in the left column helps to identify a user, since the code is printed in the header of the students working on the quiz.

One all students are finished, you can export the results to Excel.

### Questions Screen

In this screen you get a grid with all question. Here you can link/unlink questions to a quiz.

Use the search bars (*question* and *label*) to select questions and add these to the quiz.

[![image-1704215215315.png](https://www.roc.ovh/uploads/images/gallery/2024-01/scaled-1680-/image-1704215215315.png)](https://www.roc.ovh/uploads/images/gallery/2024-01/image-1704215215315.png)

### View complete quiz

On the questions screen, the first part shows the quiz data. The green dot means the quiz is active.

[![image-1704215094101.png](https://www.roc.ovh/uploads/images/gallery/2024-01/scaled-1680-/image-1704215094101.png)](https://www.roc.ovh/uploads/images/gallery/2024-01/image-1704215094101.png)

With the view button on the area where the quiz is shown, you can *view* a quiz and show all questions. This can be handy when you want to reflect on the quiz with students.

### Import/export Questions

From this screen you can go to import and export where you can import/export questions.

The format used is shown in the import screen and can also be seen when exporting a series of questions. Note that only the linked question of the quiz will be exported.

When the optional ID is provided in an import, you can update questions via import/export. Of course, you can also use the GUI.

\--