pipeline {
    agent any
    stages {
        stage('build') {
            steps {
                echo 'Building..'
            }
        }
        stage('test') {
            steps {
                echo 'Testing..'
            }
        }
        stage('SonarQube') {
            steps {
                script {
                    def scannerHome = tool 'SonarQubeScanner';
                    withSonarQubeEnv('SonarQube') {
                        sh "${scannerHome}/bin/sonar-scanner"
                    }
                }
            }
        }
        stage('deploy') {
            steps {
                echo 'Deploying....'
            }
        }
        stage('post') {
            steps {
                echo 'Post....'
            }
        }
    }
}