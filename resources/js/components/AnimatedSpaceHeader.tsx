// components/AnimatedSpaceHeader.jsx
import React from 'react';

const AnimatedSpaceHeader = () => {
  return (
    <svg
      width="100%"
      height="100%"
      viewBox="0 0 1200 300"
      preserveAspectRatio="xMidYMid meet"
      xmlns="http://www.w3.org/2000/svg"
      style={{ display: 'block', background: 'transparent' }}
    >
      <defs>
        {/* Градиенты для космоса */}
        <linearGradient id="spaceGradient" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stopColor="#0a0a2a" stopOpacity="1"/>
          <stop offset="50%" stopColor="#1a1a4a" stopOpacity="1"/>
          <stop offset="100%" stopColor="#0a0a2a" stopOpacity="1"/>
        </linearGradient>

        {/* Градиент для туманности */}
        <radialGradient id="nebulaGradient" cx="30%" cy="30%" r="70%">
          <stop offset="0%" stopColor="#4a1a6a" stopOpacity="0.8"/>
          <stop offset="50%" stopColor="#2a1a4a" stopOpacity="0.4"/>
          <stop offset="100%" stopColor="#1a1a2a" stopOpacity="0"/>
        </radialGradient>

        {/* Градиенты для планет */}
        <radialGradient id="sunGradient" cx="30%" cy="30%" r="70%">
          <stop offset="0%" stopColor="#ffdd00"/>
          <stop offset="50%" stopColor="#ffaa00"/>
          <stop offset="100%" stopColor="#ff6600"/>
        </radialGradient>

        <radialGradient id="gasGiantGradient" cx="30%" cy="30%" r="70%">
          <stop offset="0%" stopColor="#ff9966"/>
          <stop offset="50%" stopColor="#cc6633"/>
          <stop offset="100%" stopColor="#994422"/>
        </radialGradient>

        <radialGradient id="earthGradient" cx="30%" cy="30%" r="70%">
          <stop offset="0%" stopColor="#4466cc"/>
          <stop offset="50%" stopColor="#2244aa"/>
          <stop offset="100%" stopColor="#112288"/>
        </radialGradient>

        <radialGradient id="iceGiantGradient" cx="30%" cy="30%" r="70%">
          <stop offset="0%" stopColor="#aaccff"/>
          <stop offset="50%" stopColor="#88aadd"/>
          <stop offset="100%" stopColor="#6688bb"/>
        </radialGradient>

        {/* Новые градиенты для дополнительных планет */}
        <radialGradient id="lavaGradient" cx="30%" cy="30%" r="70%">
          <stop offset="0%" stopColor="#ff4422"/>
          <stop offset="50%" stopColor="#cc3322"/>
          <stop offset="100%" stopColor="#992211"/>
        </radialGradient>

        <radialGradient id="purpleGradient" cx="30%" cy="30%" r="70%">
          <stop offset="0%" stopColor="#aa44ff"/>
          <stop offset="50%" stopColor="#8833cc"/>
          <stop offset="100%" stopColor="#662299"/>
        </radialGradient>

        <radialGradient id="desertGradient" cx="30%" cy="30%" r="70%">
          <stop offset="0%" stopColor="#e8b870"/>
          <stop offset="50%" stopColor="#d4a050"/>
          <stop offset="100%" stopColor="#b88630"/>
        </radialGradient>

        {/* Стили анимаций */}
        <style>
          {`
            @keyframes float {
              0%, 100% { transform: translateY(0px) rotate(0deg); }
              50% { transform: translateY(-8px) rotate(0.5deg); }
            }

            @keyframes fly {
              0% { transform: translateX(-300px) translateY(120px) rotate(0deg); }
              25% { transform: translateX(200px) translateY(80px) rotate(1deg); }
              50% { transform: translateX(600px) translateY(100px) rotate(0deg); }
              75% { transform: translateX(1000px) translateY(60px) rotate(-1deg); }
              100% { transform: translateX(1500px) translateY(100px) rotate(0deg); }
            }

            @keyframes twinkle {
              0%, 100% { opacity: 0.3; }
              50% { opacity: 1; }
            }

            @keyframes pulse {
              0%, 100% { opacity: 0.1; }
              50% { opacity: 0.3; }
            }

            @keyframes antennaPulse {
              0%, 100% { opacity: 0.7; }
              50% { opacity: 1; }
            }

            @keyframes planetRotate {
              0% { transform: rotate(0deg); }
              100% { transform: rotate(360deg); }
            }

            @keyframes sunPulse {
              0%, 100% { r: 35; opacity: 0.9; }
              50% { r: 38; opacity: 1; }
            }

            @keyframes lavaGlow {
              0%, 100% { opacity: 0.8; }
              50% { opacity: 1; }
            }

            .satellite {
              animation: float 6s ease-in-out infinite, fly 30s linear infinite;
            }

            .distant-star {
              animation: twinkle 4s ease-in-out infinite;
            }

            .near-star {
              animation: twinkle 2.5s ease-in-out infinite;
            }

            .nebula {
              animation: pulse 10s ease-in-out infinite;
            }

            .planet-rotate {
              animation: planetRotate 40s linear infinite;
            }

            .sun-glow {
              animation: sunPulse 8s ease-in-out infinite;
            }

            .lava-glow {
              animation: lavaGlow 3s ease-in-out infinite;
            }

            .title-text {
              font-family: "Arial", sans-serif;
              font-size: 48px;
              font-weight: bold;
              fill: #ffffff;
              text-shadow: 0 0 15px #00ffff, 0 0 30px #0088ff;
            }

            .subtitle-text {
              font-family: "Arial", sans-serif;
              font-size: 24px;
              fill: #aaccff;
              text-shadow: 0 0 8px #0088ff;
            }

            .antenna-glow {
              animation: antennaPulse 3s ease-in-out infinite;
            }
          `}
        </style>
      </defs>

      {/* Фон - глубокий космос */}
      <rect width="100%" height="100%" fill="url(#spaceGradient)"/>

      {/* Солнце (далекая звезда) */}
      <g>
        <circle cx="150" cy="250" r="35" fill="url(#sunGradient)" className="sun-glow"/>
        {/* Свечение солнца */}
        <circle cx="150" cy="250" r="50" fill="#ffaa00" opacity="0.3">
          <animate attributeName="r" values="50;60;50" dur="6s" repeatCount="indefinite"/>
          <animate attributeName="opacity" values="0.2;0.4;0.2" dur="6s" repeatCount="indefinite"/>
        </circle>
        <circle cx="150" cy="250" r="70" fill="#ff6600" opacity="0.1">
          <animate attributeName="r" values="70;80;70" dur="8s" repeatCount="indefinite"/>
        </circle>
      </g>

      {/* Планета 1 - Газовый гигант с кольцами */}
      <g className="planet-rotate" transform="translate(1000, 200)">
        <circle cx="0" cy="0" r="25" fill="url(#gasGiantGradient)"/>
        {/* Полосы на газовом гиганте */}
        <ellipse cx="0" cy="-8" rx="20" ry="3" fill="#cc5533" opacity="0.8"/>
        <ellipse cx="0" cy="0" rx="22" ry="4" fill="#dd6644" opacity="0.7"/>
        <ellipse cx="0" cy="8" rx="18" ry="3" fill="#bb4422" opacity="0.9"/>

        {/* Кольца */}
        <ellipse cx="0" cy="0" rx="40" ry="8" fill="none" stroke="#d4a86a" strokeWidth="3" opacity="0.7" transform="rotate(25)"/>
        <ellipse cx="0" cy="0" rx="35" ry="6" fill="none" stroke="#e8c89a" strokeWidth="2" opacity="0.8" transform="rotate(25)"/>
        <ellipse cx="0" cy="0" rx="30" ry="4" fill="none" stroke="#f8e8c8" strokeWidth="1" opacity="0.9" transform="rotate(25)"/>

        {/* Спутники */}
        <circle cx="45" cy="-15" r="3" fill="#cccccc">
          <animateTransform attributeName="transform" type="rotate" from="0 0 0" to="360 0 0" dur="15s" repeatCount="indefinite"/>
        </circle>
        <circle cx="-35" cy="20" r="2" fill="#aaaaaa">
          <animateTransform attributeName="transform" type="rotate" from="360 0 0" to="0 0 0" dur="12s" repeatCount="indefinite"/>
        </circle>
      </g>

      {/* Планета 2 - Землеподобная */}
      <g className="planet-rotate" transform="translate(400, 50)" style={{ animationDuration: '60s' }}>
        <circle cx="0" cy="0" r="18" fill="url(#earthGradient)"/>

        {/* Континенты */}
        <path d="M -12,-5 Q -8,-8 -5,-5 Q -2,-8 2,-6 Q 5,-3 8,-4 Q 12,-2 12,2 Q 10,6 6,8 Q 2,10 -3,8 Q -8,6 -10,2 Q -12,-1 -12,-5 Z"
              fill="#44aa44" opacity="0.7"/>

        {/* Облака */}
        <ellipse cx="-5" cy="-8" rx="4" ry="2" fill="#ffffff" opacity="0.6">
          <animate attributeName="cx" values="-5;-3;-5" dur="10s" repeatCount="indefinite"/>
        </ellipse>
        <ellipse cx="8" cy="4" rx="3" ry="1.5" fill="#ffffff" opacity="0.5">
          <animate attributeName="cy" values="4;6;4" dur="8s" repeatCount="indefinite"/>
        </ellipse>

        {/* Орбитальная станция */}
        <circle cx="25" cy="0" r="1.5" fill="#ff4444">
          <animateTransform attributeName="transform" type="rotate" from="0 0 0" to="360 0 0" dur="8s" repeatCount="indefinite"/>
        </circle>
      </g>

      {/* Планета 3 - Ледяной гигант */}
      <g transform="translate(800, 80)">
        <circle cx="0" cy="0" r="15" fill="url(#iceGiantGradient)"/>

        {/* Ледяные шапки */}
        <circle cx="0" cy="-10" r="8" fill="#ffffff" opacity="0.8"/>
        <circle cx="0" cy="10" r="6" fill="#ffffff" opacity="0.8"/>

        {/* Атмосферные полосы */}
        <ellipse cx="0" cy="-3" rx="12" ry="2" fill="#aaddff" opacity="0.6"/>
        <ellipse cx="0" cy="3" rx="10" ry="1.5" fill="#bbeeff" opacity="0.7"/>

        {/* Вращение */}
        <animateTransform attributeName="transform" type="rotate" from="0 0 0" to="360 0 0" dur="25s" repeatCount="indefinite"/>
      </g>

      {/* НОВЫЕ ПЛАНЕТЫ С ПРАВОГО КРАЯ - ИСПРАВЛЕННЫЕ КООРДИНАТЫ */}

      {/* Планета 4 - Лавовая планета (правый верхний угол) */}
      <g transform="translate(1050, 60)">
        <circle cx="0" cy="0" r="12" fill="url(#lavaGradient)" className="lava-glow"/>

        {/* Лавовые реки */}
        <path d="M -8,-5 Q -4,-8 0,-6 Q 4,-4 8,-5" fill="#ff2200" opacity="0.9" stroke="#ff6600" strokeWidth="0.5">
          <animate attributeName="opacity" values="0.7;1;0.7" dur="2s" repeatCount="indefinite"/>
        </path>
        <path d="M -10,3 Q -5,6 0,4 Q 5,2 10,3" fill="#ff4422" opacity="0.8">
          <animate attributeName="opacity" values="0.6;0.9;0.6" dur="1.5s" repeatCount="indefinite"/>
        </path>

        {/* Вулканы */}
        <path d="M -5,8 L -3,5 L -1,8 Z" fill="#552211"/>
        <path d="M 3,7 L 5,4 L 7,7 Z" fill="#442211"/>

        {/* Свечение */}
        <circle cx="0" cy="0" r="14" fill="#ff4422" opacity="0.2">
          <animate attributeName="r" values="14;16;14" dur="3s" repeatCount="indefinite"/>
        </circle>

        {/* Вращение */}
        <animateTransform attributeName="transform" type="rotate" from="0 0 0" to="360 0 0" dur="20s" repeatCount="indefinite"/>
      </g>

      {/* Планета 5 - Фиолетовая планета с астероидным кольцом (правый нижний угол) */}
      <g transform="translate(1100, 220)">
        <circle cx="0" cy="0" r="16" fill="url(#purpleGradient)"/>

        {/* Текстура поверхности */}
        <circle cx="-5" cy="-4" r="2" fill="#9933cc" opacity="0.7"/>
        <circle cx="6" cy="3" r="1.5" fill="#8833bb" opacity="0.8"/>
        <circle cx="0" cy="8" r="2.5" fill="#7733aa" opacity="0.6"/>
        <circle cx="-8" cy="6" r="1" fill="#aa44dd" opacity="0.9"/>

        {/* Астероидное кольцо */}
        <g>
          <circle cx="30" cy="0" r="1.2" fill="#a0a0a0">
            <animateTransform attributeName="transform" type="rotate" from="0 0 0" to="360 0 0" dur="18s" repeatCount="indefinite"/>
          </circle>
          <circle cx="25" cy="-15" r="0.8" fill="#b0b0b0">
            <animateTransform attributeName="transform" type="rotate" from="0 0 0" to="360 0 0" dur="15s" repeatCount="indefinite"/>
          </circle>
          <circle cx="-20" cy="20" r="1" fill="#909090">
            <animateTransform attributeName="transform" type="rotate" from="360 0 0" to="0 0 0" dur="20s" repeatCount="indefinite"/>
          </circle>
        </g>

        {/* Спутник */}
        <circle cx="40" cy="-10" r="1.5" fill="#d0d0d0">
          <animateTransform attributeName="transform" type="rotate" from="0 0 0" to="360 0 0" dur="12s" repeatCount="indefinite"/>
        </circle>

        {/* Вращение */}
        <animateTransform attributeName="transform" type="rotate" from="0 0 0" to="360 0 0" dur="35s" repeatCount="indefinite"/>
      </g>

      {/* Планета 6 - Пустынная планета (правый средний край) */}
      <g transform="translate(1150, 120)">
        <circle cx="0" cy="0" r="14" fill="url(#desertGradient)"/>

        {/* Кратеры */}
        <circle cx="-8" cy="-5" r="3" fill="#a08040" stroke="#806030" strokeWidth="0.5"/>
        <circle cx="5" cy="6" r="2" fill="#b09050" stroke="#907040" strokeWidth="0.5"/>
        <circle cx="7" cy="-7" r="2.5" fill="#987840" stroke="#786030" strokeWidth="0.5"/>

        {/* Дюны */}
        <ellipse cx="-3" cy="8" rx="6" ry="2" fill="#d4b880" opacity="0.8"/>
        <ellipse cx="10" cy="0" rx="4" ry="1.5" fill="#e8cc90" opacity="0.7"/>

        {/* Оазис */}
        <circle cx="-10" cy="3" r="1.5" fill="#4466aa" opacity="0.9">
          <animate attributeName="r" values="1.5;2;1.5" dur="4s" repeatCount="indefinite"/>
        </circle>

        {/* Вращение */}
        <animateTransform attributeName="transform" type="rotate" from="0 0 0" to="360 0 0" dur="30s" repeatCount="indefinite"/>
      </g>

      {/* Далекие звезды */}
      {Array.from({ length: 80 }, (_, i) => {
        const x = Math.random() * 1200;
        const y = Math.random() * 300;
        const size = (Math.random() * 0.8 + 0.2);
        const delay = Math.random() * 4;

        return (
          <circle
            key={`distant-${i}`}
            cx={x}
            cy={y}
            r={size}
            fill="#ffffff"
            className="distant-star"
            style={{ animationDelay: `${delay}s` }}
          />
        );
      })}

      {/* Ближние звезды */}
      {Array.from({ length: 50 }, (_, i) => {
        const x = Math.random() * 1200;
        const y = Math.random() * 300;
        const size = (Math.random() * 1.5 + 0.5);
        const delay = Math.random() * 3;
        const duration = Math.random() * 3 + 2;

        return (
          <circle
            key={`near-${i}`}
            cx={x}
            cy={y}
            r={size}
            fill="#ffffcc"
            className="near-star"
            style={{ animationDelay: `${delay}s` }}
          >
            <animate
              attributeName="r"
              values={`${size};${size * 1.3};${size}`}
              dur={`${duration}s`}
              repeatCount="indefinite"
            />
          </circle>
        );
      })}

      {/* Туманности */}
      <ellipse
        cx="900"
        cy="80"
        rx="180"
        ry="60"
        fill="url(#nebulaGradient)"
        className="nebula"
      />
      <ellipse
        cx="300"
        cy="220"
        rx="120"
        ry="50"
        fill="#1a2a5a"
        opacity="0.15"
        className="nebula"
        style={{ animationDelay: '-5s' }}
      />

      {/* Спутник Вояджер (без двигателя) */}
      <g className="satellite">
        {/* Основной десятигранный корпус */}
        <path
          d="M -20,-8 L -15,-10 L -10,-12 L 10,-12 L 15,-10 L 20,-8 L 20,8 L 15,10 L 10,12 L -10,12 L -15,10 L -20,8 Z"
          fill="#c0c0c0"
          stroke="#909090"
          strokeWidth="1"
        />

        {/* Золотая пластина с записями */}
        <rect x="-18" y="-6" width="36" height="12" fill="#ffd700" stroke="#b39700" strokeWidth="0.5" rx="1"/>

        {/* Научные приборы */}
        <circle cx="-12" cy="0" r="3" fill="#2a4a8a"/>
        <circle cx="12" cy="0" r="3" fill="#2a4a8a"/>
        <circle cx="0" cy="-5" r="2" fill="#3a5a9a"/>
        <circle cx="0" cy="5" r="2" fill="#3a5a9a"/>

        {/* Большая антенна */}
        <line x1="0" y1="-12" x2="0" y2="-35" stroke="#d0d0d0" strokeWidth="1.5"/>
        <circle cx="0" cy="-35" r="4" fill="#e8e8e8" className="antenna-glow"/>

        {/* Вторая антенна */}
        <line x1="-5" y1="-10" x2="-15" y2="-25" stroke="#c8c8c8" strokeWidth="1"/>
        <circle cx="-15" cy="-25" r="2" fill="#e0e0e0"/>

        {/* Топливные баки */}
        <ellipse cx="-25" cy="0" rx="3" ry="8" fill="#a0a0a0" transform="rotate(15 -25 0)"/>
        <ellipse cx="25" cy="0" rx="3" ry="8" fill="#a0a0a0" transform="rotate(-15 25 0)"/>

        {/* Солнечные панели */}
        <rect x="-40" y="-15" width="8" height="30" fill="#2a3a7a" transform="rotate(20 0 0)"/>
        <rect x="32" y="-15" width="8" height="30" fill="#2a3a7a" transform="rotate(-20 0 0)"/>

        {/* Дополнительные антенны */}
        <line x1="18" y1="-8" x2="28" y2="-18" stroke="#b8b8b8" strokeWidth="0.8"/>
        <line x1="18" y1="8" x2="28" y2="18" stroke="#b8b8b8" strokeWidth="0.8"/>

        {/* Мигающие научные огни */}
        <circle cx="-8" cy="-8" r="1" fill="#ff4444">
          <animate
            attributeName="opacity"
            values="0;1;0"
            dur="2s"
            repeatCount="indefinite"
          />
        </circle>
        <circle cx="8" cy="8" r="1" fill="#44ff44">
          <animate
            attributeName="opacity"
            values="1;0;1"
            dur="1.8s"
            repeatCount="indefinite"
          />
        </circle>
      </g>

      {/* Заголовок */}
      <text x="600" y="100" textAnchor="middle" className="title-text">
        Galactic Explorer
        <animate
          attributeName="opacity"
          values="0.7;1;0.7"
          dur="4s"
          repeatCount="indefinite"
        />
      </text>

      {/* Подзаголовок */}
      <text x="600" y="140" textAnchor="middle" className="subtitle-text">
        Chart the Unknown, Name the Uncharted
        <animate
          attributeName="opacity"
          values="0.5;0.8;0.5"
          dur="5s"
          repeatCount="indefinite"
        />
      </text>
    </svg>
  );
};

export default AnimatedSpaceHeader;
