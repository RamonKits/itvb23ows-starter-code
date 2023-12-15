pipeline {
    agent { docker { image 'php:apache' } }
    stages {
        stage('build') {
            steps {
                sh 'php --version'
            }
        }
    }
}