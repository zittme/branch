# Zittme Branch

[Zittme](https://github.com/zittme/zittme) 엔진용 다점포(지점) 관리 공용 모듈입니다. 매장과 지점 정보를 한 곳에서 관리하고, 예약·숙박 같은 다른 모듈이 지점 번호로 참조해 씁니다.

## 요구 사항

- Zittme 1.0.0 이상

## 설치

Zittme 설치 경로의 `modules/branch` 에 이 저장소의 내용을 놓습니다.

```bash
cd 설치경로/modules
git clone https://github.com/zittme/branch.git branch
```

압축 파일로 받았다면 `modules/branch/` 에 풀면 됩니다. 이후 관리자 화면에 접속하면 테이블 생성이 자동으로 진행됩니다.

## 주요 기능

- 지점 등록: 코드, 주소와 좌표, 전화, 지역, 편의시설, 사진, 소개(에디터)
- 요일별 영업시간과 브레이크타임, 자정을 넘기는 영업
- 휴무일: 특정 날짜, 매달 n번째 요일 반복
- 지금 영업 중 표시, 지역별 목록, 찾아오는 길
- 지점 이름·주소 등 칸별 다국어 입력
- 다른 모듈 연동: [예약](https://github.com/zittme/reservation)(지점별 담당자·예약), [숙박](https://github.com/zittme/lodging)과 함께 쓰면 다점포 운영
- 스킨 방식의 프론트 화면 (기본 스킨 포함)

## 라이선스

[GPL v2](LICENSE)

## 문의

- 홈페이지: https://zitt.me
- 매뉴얼: https://zitt.me/manual
