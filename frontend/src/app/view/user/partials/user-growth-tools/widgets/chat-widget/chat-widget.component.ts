import { Component, Input, OnInit } from '@angular/core';

@Component({
  selector: 'app-chat-widget',
  templateUrl: './chat-widget.component.html',
  styleUrls: [
    './chat-widget.component.scss',
    '../../../../../../../assets/scss/widget.scss'
  ]
})
export class ChatWidgetComponent implements OnInit {
  @Input() public config;
  @Input() public view;
  @Input() public tabsValue;

  constructor() { }

  ngOnInit() {
  }

}
