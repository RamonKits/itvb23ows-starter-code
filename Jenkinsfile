pipeline {
    agent any
    stages {
        stage('build') {
            steps {
                script {
                    docker.image('php:apache').inside {
                        sh 'php --version'
                    }
                }
            }
        }
    }
}
