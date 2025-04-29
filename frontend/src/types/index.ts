// src/types/index.ts

// Modèles de données
export interface User {
    id: number;
    name: string;
    email: string;
    role_id: number;
    role?: Role;
    active: boolean;
    created_at?: string;
    updated_at?: string;
  }
  
  export interface Role {
    id: number;
    name: string;
    description: string;
  }
  
  export interface Questionnaire {
    id: number;
    title: string;
    description?: string;
    nb_question: number;
    creation_date: string;
    last_modification: string;
    active: boolean;
    questions?: Question[];
  }
  
  export interface Question {
    id: number;
    questionnaire_id: number;
    response_text: string;
    response_score: number;
    date_response: string;
  }
  
  export interface Response {
    id: number;
    user_id: number;
    question_id: number;
    reponse: string;
    date: string;
  }
  
  export interface Diagnostic {
    id: number;
    user_id: number;
    score_total: number;
    stress_level: string;
    diagnostic_date: string;
    consequences?: string;
    advices?: string;
  }
  
  // Types pour les formulaires
  export interface LoginForm {
    email: string;
    password: string;
  }
  
  export interface RegisterForm extends LoginForm {
    name: string;
    password_confirmation: string;
  }
  
  export interface DiagnosticForm {
    questions: number[];
    score_total: number;
    stress_level: string;
  }