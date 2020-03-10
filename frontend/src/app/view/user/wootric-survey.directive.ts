import { AfterViewInit, Directive, ElementRef } from '@angular/core';
import { SharedService } from '../../services/shared.service';

@Directive({
  selector: 'wootric-survey'
})
export class WootricSurveyDirective implements AfterViewInit {

  constructor(
    private elementRef: ElementRef,
    private sharedService: SharedService
  ) {
    window['wootricSettings'] = {
      email: this.sharedService.userInfo.email,
      created_at: Math.round(new Date(this.sharedService.userInfo.created).getTime() / 1000),
      account_token: 'NPS-28d3e2ea'
    };
  }

  ngAfterViewInit() {
    const script = document.createElement('script');

    script.type = 'text/javascript';
    script.src = 'https://cdn.wootric.com/wootric-sdk.js';
    script.async = true;
    script.onload = function() {
      window['WootricSurvey'].run();
    };

    this.elementRef.nativeElement.appendChild(script);
  }

}
